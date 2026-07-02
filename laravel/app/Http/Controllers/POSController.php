<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\BranchStock;
use App\Models\StockMovement;
use App\Models\Customer;
use App\Models\CashRegister;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class POSController extends Controller
{
    /**
     * Look up product variant by barcode, SKU, or name.
     */
    public function lookupVariant(Request $request)
    {
        $request->validate(['query' => 'required|string']);
        $q = $request->input('query');

        $variant = ProductVariant::whereHas('product')
            ->where(function ($query) use ($q) {
                $query->where('barcode', $q)
                    ->orWhere('sku', $q)
                    ->orWhereHas('product', function ($subQuery) use ($q) {
                        $subQuery->where('name', 'like', "%{$q}%");
                    });
            })
            ->with(['product.unit'])
            ->first();

        if (!$variant) {
            return response()->json(['error' => 'Product variant not found.'], 404);
        }

        return response()->json($variant);
    }

    /**
     * Submit/Checkout a sale.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'branch_id'     => 'required|exists:branches,id',
            'customer_id'   => 'nullable|exists:customers,id',
            'hold_status'   => 'sometimes|in:active,completed',
            'tax_amount'    => 'sometimes|numeric|min:0',
            'discount_amount'=> 'sometimes|numeric|min:0',
            'items'         => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity'           => 'required|integer|min:1',
            'items.*.unit_price'         => 'required|numeric|min:0',
            'items.*.tax_amount'         => 'sometimes|numeric|min:0',
            'items.*.discount_amount'    => 'sometimes|numeric|min:0',
            'payments'      => 'required_if:hold_status,completed|array',
            'payments.*.amount'          => 'required|numeric|min:0',
            'payments.*.payment_method'  => 'required|string',
            'payments.*.transaction_id'  => 'nullable|string',
        ]);

        // 0. Ensure branch, customer, and all products belong to this tenant shop
        if (!\App\Models\Branch::where('id', $request->branch_id)->exists()) {
            return response()->json(['error' => 'Invalid branch.'], 403);
        }

        if ($request->customer_id && !\App\Models\Customer::where('id', $request->customer_id)->exists()) {
            return response()->json(['error' => 'Invalid customer.'], 403);
        }

        foreach ($request->items as $item) {
            $variantExists = ProductVariant::where('id', $item['product_variant_id'])
                ->whereHas('product')
                ->exists();
            if (!$variantExists) {
                return response()->json(['error' => 'Invalid product variant selected.'], 403);
            }
        }

        $user = auth()->user();
        
        // 1. Verify cashier has an OPEN cash register drawer
        $register = CashRegister::where('user_id', $user->id)
            ->where('branch_id', $request->branch_id)
            ->where('status', 'open')
            ->first();

        $holdStatus = $request->hold_status ?? 'completed';

        if ($holdStatus === 'completed' && !$register) {
            return response()->json([
                'error' => 'Drawer Access Blocked: You must open a Cash Register shift drawer before checkout.'
            ], 403);
        }

        $sale = DB::transaction(function () use ($request, $user, $holdStatus) {
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += ($item['quantity'] * $item['unit_price']) + ($item['tax_amount'] ?? 0.00) - ($item['discount_amount'] ?? 0.00);
            }
            $totalAmount += $request->tax_amount ?? 0.00;
            $totalAmount -= $request->discount_amount ?? 0.00;

            // Generate unique invoice number
            $invoiceNo = 'INV-' . strtoupper(Str::random(8)) . '-' . time();

            // Set primary payment method flag (e.g. Split or the only one selected)
            $method = 'Cash';
            if ($holdStatus === 'completed' && count($request->payments) > 0) {
                $method = count($request->payments) > 1 ? 'Split' : $request->payments[0]['payment_method'];
            }

            // Create sale header
            $sale = Sale::create([
                'shop_id'         => $user->shop_id,
                'branch_id'       => $request->branch_id,
                'user_id'         => $user->id,
                'customer_id'     => $request->customer_id,
                'invoice_number'  => $invoiceNo,
                'total_amount'    => $totalAmount,
                'tax_amount'      => $request->tax_amount ?? 0.00,
                'discount_amount' => $request->discount_amount ?? 0.00,
                'payment_status'  => $holdStatus === 'completed' ? 'Paid' : 'Unpaid',
                'payment_method'  => $method,
                'hold_status'      => $holdStatus,
            ]);

            // Create sale lines
            foreach ($request->items as $item) {
                $subtotal = ($item['quantity'] * $item['unit_price']) + ($item['tax_amount'] ?? 0.00) - ($item['discount_amount'] ?? 0.00);
                $sale->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                    'tax_amount'         => $item['tax_amount'] ?? 0.00,
                    'discount_amount'    => $item['discount_amount'] ?? 0.00,
                    'subtotal'           => $subtotal,
                ]);

                // Deduct stock immediately if sale is COMPLETED (not on hold)
                if ($holdStatus === 'completed') {
                    $stock = BranchStock::where('branch_id', $request->branch_id)
                        ->where('product_variant_id', $item['product_variant_id'])
                        ->first();

                    if ($stock) {
                        $stock->decrement('quantity', $item['quantity']);
                    }

                    // Record negative movement log
                    StockMovement::create([
                        'shop_id'            => $user->shop_id,
                        'branch_id'          => $request->branch_id,
                        'product_variant_id' => $item['product_variant_id'],
                        'quantity'           => -$item['quantity'],
                        'type'               => 'Sale',
                        'reference_id'       => $sale->id,
                    ]);
                }
            }

            // Create payments if completed
            $paidTotal = 0;
            if ($holdStatus === 'completed') {
                foreach ($request->payments as $payData) {
                    $paidTotal += $payData['amount'];
                    Payment::create([
                        'shop_id'        => $user->shop_id,
                        'sale_id'        => $sale->id,
                        'amount'         => $payData['amount'],
                        'payment_method' => $payData['payment_method'],
                        'transaction_id' => $payData['transaction_id'] ?? null,
                    ]);
                }

                // If customer is selected
                if ($request->customer_id) {
                    $customer = Customer::find($request->customer_id);
                    if ($customer) {
                        // 1. Award loyalty points (1 point per $10 spent)
                        $points = floor($totalAmount / 10);
                        $customer->increment('loyalty_points', $points);

                        // 2. Handle customer ledger: if paid less than total, add to customer balance (debt)
                        $debt = $totalAmount - $paidTotal;
                        if ($debt > 0) {
                            $customer->increment('balance', $debt);
                            $sale->update(['payment_status' => 'Partial']);
                        }
                    }
                }
            }

            return $sale;
        });

        return response()->json([
            'message' => $holdStatus === 'completed' ? 'Sale processed successfully.' : 'Sale placed on hold.',
            'sale'    => $sale->load(['items.productVariant.product', 'payments'])
        ]);
    }

    /**
     * List held sales.
     */
    public function listHoldSales()
    {
        $sales = Sale::where('hold_status', 'active')
            ->with(['items.productVariant.product', 'customer'])
            ->get();
        return response()->json($sales);
    }

    /**
     * Resume and complete a hold sale.
     */
    public function resumeSale(Request $request, $id)
    {
        $request->validate([
            'payments'     => 'required|array|min:1',
            'payments.*.amount'          => 'required|numeric|min:0',
            'payments.*.payment_method'  => 'required|string',
            'payments.*.transaction_id'  => 'nullable|string',
        ]);

        $sale = Sale::with('items')->findOrFail($id);
        if ($sale->hold_status !== 'active') {
            return response()->json(['error' => 'Sale is not on hold.'], 400);
        }

        $user = auth()->user();

        // Check open register
        $register = CashRegister::where('user_id', $user->id)
            ->where('branch_id', $sale->branch_id)
            ->where('status', 'open')
            ->first();

        if (!$register) {
            return response()->json(['error' => 'You must open a Cash Register drawer before resuming checkout.'], 403);
        }

        DB::transaction(function () use ($request, $sale, $user) {
            $sale->update([
                'hold_status'    => 'completed',
                'payment_status' => 'Paid',
            ]);

            // Deduct stock and write movements
            foreach ($sale->items as $item) {
                $stock = BranchStock::where('branch_id', $sale->branch_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->first();

                if ($stock) {
                    $stock->decrement('quantity', $item->quantity);
                }

                StockMovement::create([
                    'shop_id'            => $user->shop_id,
                    'branch_id'          => $sale->branch_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity'           => -$item->quantity,
                    'type'               => 'Sale',
                    'reference_id'       => $sale->id,
                ]);
            }

            // Create payments
            $paidTotal = 0;
            foreach ($request->payments as $payData) {
                $paidTotal += $payData['amount'];
                Payment::create([
                    'shop_id'        => $user->shop_id,
                    'sale_id'        => $sale->id,
                    'amount'         => $payData['amount'],
                    'payment_method' => $payData['payment_method'],
                    'transaction_id' => $payData['transaction_id'] ?? null,
                ]);
            }

            // Update customer balance if debt
            if ($sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                if ($customer) {
                    $points = floor($sale->total_amount / 10);
                    $customer->increment('loyalty_points', $points);

                    $debt = $sale->total_amount - $paidTotal;
                    if ($debt > 0) {
                        $customer->increment('balance', $debt);
                        $sale->update(['payment_status' => 'Partial']);
                    }
                }
            }
        });

        return response()->json(['message' => 'Sale resumed and completed successfully.']);
    }

    /**
     * Process a sale return / refund.
     */
    public function refundSale($id)
    {
        $sale = Sale::with('items')->findOrFail($id);
        if ($sale->payment_status === 'Refunded') {
            return response()->json(['error' => 'This sale has already been refunded.'], 400);
        }

        DB::transaction(function () use ($sale) {
            $sale->update(['payment_status' => 'Refunded']);

            // Restore inventory and write stock movement
            foreach ($sale->items as $item) {
                $stock = BranchStock::firstOrCreate([
                    'shop_id'            => $sale->shop_id,
                    'branch_id'          => $sale->branch_id,
                    'product_variant_id' => $item->product_variant_id,
                ], [
                    'quantity'        => 0,
                    'low_stock_alert' => 10
                ]);

                $stock->increment('quantity', $item->quantity);

                StockMovement::create([
                    'shop_id'            => $sale->shop_id,
                    'branch_id'          => $sale->branch_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity'           => $item->quantity,
                    'type'               => 'Sale', // Refund counts as positive adjustment under sales movement
                    'reference_id'       => $sale->id,
                ]);
            }

            // Deduct customer loyalty points and ledger balance if debt was added
            if ($sale->customer_id) {
                $customer = $sale->customer;
                if ($customer) {
                    $points = floor($sale->total_amount / 10);
                    $customer->decrement('loyalty_points', min($customer->loyalty_points, $points));

                    // If they had credit debt, deduct it
                    $paidAmount = $sale->payments()->sum('amount');
                    $debt = $sale->total_amount - $paidAmount;
                    if ($debt > 0) {
                        $customer->decrement('balance', min($customer->balance, $debt));
                    }
                }
            }
        });

        return response()->json(['message' => 'Invoice refunded successfully. Inventory restored.']);
    }
}
