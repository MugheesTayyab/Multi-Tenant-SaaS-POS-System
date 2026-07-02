<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BranchStock;
use App\Models\StockMovement;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display current stock status across branches.
     */
    public function listStock(Request $request)
    {
        $query = BranchStock::with(['branch', 'productVariant.product']);

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        return response()->json($query->get());
    }

    /**
     * Adjust stock quantities (Stock In, Stock Out, Adjustments, Damage).
     */
    public function stockAdjustment(Request $request)
    {
        $request->validate([
            'branch_id'          => 'required|exists:branches,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity'           => 'required|integer', // positive or negative delta
            'type'               => 'required|in:Adjustment,Damage,StockIn,StockOut',
            'notes'              => 'nullable|string',
        ]);

        $movement = DB::transaction(function () use ($request) {
            $stock = BranchStock::firstOrCreate([
                'shop_id'            => auth()->user()->shop_id,
                'branch_id'          => $request->branch_id,
                'product_variant_id' => $request->product_variant_id,
            ], [
                'quantity'        => 0,
                'low_stock_alert' => 10
            ]);

            // Add the delta
            $stock->quantity += $request->quantity;
            
            if ($stock->quantity < 0) {
                // Prevent negative stock unless specifically permitted, but in POS it's safer to prevent or allow with a warnings
                throw new \Exception("Insufficient inventory: Stock cannot drop below zero. Current quantity is " . ($stock->quantity - $request->quantity));
            }
            
            $stock->save();

            // Record immutable movement log
            return StockMovement::create([
                'shop_id'            => auth()->user()->shop_id,
                'branch_id'          => $request->branch_id,
                'product_variant_id' => $request->product_variant_id,
                'quantity'           => $request->quantity,
                'type'               => $request->type,
                'reference_id'       => $stock->id,
            ]);
        });

        return response()->json(['message' => 'Inventory adjusted successfully.', 'movement' => $movement]);
    }

    /**
     * Transfer stock between physical branches.
     */
    public function stockTransfer(Request $request)
    {
        $request->validate([
            'from_branch_id'     => 'required|exists:branches,id|different:to_branch_id',
            'to_branch_id'       => 'required|exists:branches,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity'           => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Deduct from source branch
            $sourceStock = BranchStock::where('branch_id', $request->from_branch_id)
                ->where('product_variant_id', $request->product_variant_id)
                ->first();

            if (!$sourceStock || $sourceStock->quantity < $request->quantity) {
                throw new \Exception("Insufficient stock in source branch for transfer.");
            }

            $sourceStock->decrement('quantity', $request->quantity);

            // Record Transfer-Out movement
            StockMovement::create([
                'shop_id'            => auth()->user()->shop_id,
                'branch_id'          => $request->from_branch_id,
                'product_variant_id' => $request->product_variant_id,
                'quantity'           => -$request->quantity,
                'type'               => 'Transfer',
                'reference_id'       => $sourceStock->id,
            ]);

            // 2. Add to destination branch
            $destStock = BranchStock::firstOrCreate([
                'shop_id'            => auth()->user()->shop_id,
                'branch_id'          => $request->to_branch_id,
                'product_variant_id' => $request->product_variant_id,
            ], [
                'quantity'        => 0,
                'low_stock_alert' => 10
            ]);

            $destStock->increment('quantity', $request->quantity);

            // Record Transfer-In movement
            StockMovement::create([
                'shop_id'            => auth()->user()->shop_id,
                'branch_id'          => $request->to_branch_id,
                'product_variant_id' => $request->product_variant_id,
                'quantity'           => $request->quantity,
                'type'               => 'Transfer',
                'reference_id'       => $destStock->id,
            ]);
        });

        return response()->json(['message' => 'Stock transferred successfully.']);
    }

    /**
     * Retrieve global movements audit log.
     */
    public function movements()
    {
        $movements = StockMovement::with(['branch', 'productVariant.product'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($movements);
    }
}
