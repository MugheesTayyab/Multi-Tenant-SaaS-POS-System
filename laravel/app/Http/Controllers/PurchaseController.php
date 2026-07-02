<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\BranchStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    /**
     * List all purchase orders.
     */
    public function listPurchases()
    {
        return response()->json(Purchase::with(['supplier', 'branch', 'user', 'items.productVariant.product'])->get());
    }

    /**
     * Create a new purchase order.
     */
    public function storePurchase(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'branch_id'   => 'required|exists:branches,id',
            'paid_amount' => 'sometimes|numeric|min:0',
            'items'       => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity'           => 'required|integer|min:1',
            'items.*.cost_price'         => 'required|numeric|min:0',
        ]);

        // Ensure branch and supplier belong to this tenant shop
        if (!\App\Models\Branch::where('id', $request->branch_id)->exists()) {
            return response()->json(['error' => 'Invalid branch.'], 403);
        }

        if (!\App\Models\Supplier::where('id', $request->supplier_id)->exists()) {
            return response()->json(['error' => 'Invalid supplier.'], 403);
        }

        foreach ($request->items as $item) {
            $variantExists = \App\Models\ProductVariant::where('id', $item['product_variant_id'])
                ->whereHas('product')
                ->exists();
            if (!$variantExists) {
                return response()->json(['error' => 'Invalid product variant selected.'], 403);
            }
        }

        $purchase = DB::transaction(function () use ($request) {
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['cost_price'];
            }

            // Generate unique PO number
            $poNumber = 'PO-' . strtoupper(Str::random(8)) . '-' . time();

            // Create purchase header
            $purchase = Purchase::create([
                'shop_id'         => auth()->user()->shop_id,
                'branch_id'       => $request->branch_id,
                'supplier_id'     => $request->supplier_id,
                'user_id'         => auth()->id(),
                'purchase_number' => $poNumber,
                'status'          => 'pending', // pending -> approved -> received
                'total_amount'    => $totalAmount,
                'paid_amount'     => $request->paid_amount ?? 0.00,
            ]);

            // Create purchase items
            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['cost_price'];
                $purchase->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                    'cost_price'         => $item['cost_price'],
                    'subtotal'           => $subtotal,
                ]);
            }

            return $purchase;
        });

        return response()->json($purchase->load('items'));
    }

    /**
     * Approve a purchase order.
     */
    public function approvePurchase($id)
    {
        $purchase = Purchase::findOrFail($id);
        if ($purchase->status !== 'pending') {
            return response()->json(['error' => 'Purchase order already processed beyond pending state.'], 400);
        }

        $purchase->update(['status' => 'approved']);
        return response()->json(['message' => 'Purchase order approved successfully.']);
    }

    /**
     * Log Goods Received Note (GRN) and update inventory.
     */
    public function receiveGoods($id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);
        if ($purchase->status !== 'approved' && $purchase->status !== 'pending') {
            return response()->json(['error' => 'Purchase order is either already received or not approved.'], 400);
        }

        DB::transaction(function () use ($purchase) {
            $purchase->update(['status' => 'received']);

            // Update inventory and log stock movements
            foreach ($purchase->items as $item) {
                $stock = BranchStock::firstOrCreate([
                    'shop_id'            => $purchase->shop_id,
                    'branch_id'          => $purchase->branch_id,
                    'product_variant_id' => $item->product_variant_id,
                ], [
                    'quantity'        => 0,
                    'low_stock_alert' => 10
                ]);

                $stock->increment('quantity', $item->quantity);

                // Add immutable movement trail
                StockMovement::create([
                    'shop_id'            => $purchase->shop_id,
                    'branch_id'          => $purchase->branch_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity'           => $item->quantity,
                    'type'               => 'Purchase',
                    'reference_id'       => $purchase->id,
                ]);
            }

            // Update Supplier ledger balance (accounts payable increases by remaining balance)
            $supplier = $purchase->supplier;
            $unpaidAmount = $purchase->total_amount - $purchase->paid_amount;
            if ($unpaidAmount > 0) {
                $supplier->increment('balance', $unpaidAmount);
            }
        });

        return response()->json(['message' => 'Goods Received Note registered successfully. Inventory levels updated.']);
    }

    /**
     * Record a Purchase Return.
     */
    public function storeReturn(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'items'       => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity'           => 'required|integer|min:1',
        ]);

        $purchase = Purchase::findOrFail($request->purchase_id);
        if ($purchase->status !== 'received') {
            return response()->json(['error' => 'Can only return items on received purchases.'], 400);
        }

        DB::transaction(function () use ($request, $purchase) {
            $refundVal = 0;
            foreach ($request->items as $returnItem) {
                $poLine = PurchaseItem::where('purchase_id', $purchase->id)
                    ->where('product_variant_id', $returnItem['product_variant_id'])
                    ->first();

                if (!$poLine || $poLine->quantity < $returnItem['quantity']) {
                    throw new \Exception("Cannot return more items than purchased.");
                }

                // Decrement stock
                $stock = BranchStock::where('branch_id', $purchase->branch_id)
                    ->where('product_variant_id', $returnItem['product_variant_id'])
                    ->first();

                if ($stock) {
                    $stock->decrement('quantity', $returnItem['quantity']);
                }

                // Record negative stock movement
                StockMovement::create([
                    'shop_id'            => $purchase->shop_id,
                    'branch_id'          => $purchase->branch_id,
                    'product_variant_id' => $returnItem['product_variant_id'],
                    'quantity'           => -$returnItem['quantity'],
                    'type'               => 'Adjustment', // Negative adjustment
                    'reference_id'       => $purchase->id,
                ]);

                $refundVal += $returnItem['quantity'] * $poLine->cost_price;
            }

            // Deduct supplier payable balance
            $supplier = $purchase->supplier;
            $supplier->decrement('balance', $refundVal);
        });

        return response()->json(['message' => 'Purchase return processed successfully. Supplier credit updated.']);
    }
}
