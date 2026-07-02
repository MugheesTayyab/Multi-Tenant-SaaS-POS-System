<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Branch;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    // === BRANCH MANAGEMENT ===
    public function listBranches()
    {
        return response()->json(Branch::all());
    }

    public function storeBranch(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $shop = auth()->user()->shop;
        
        // CHECK PLAN BRANCH LIMITS
        if ($shop) {
            $plan = $shop->subscription;
            if ($plan) {
                $branchCount = Branch::count();
                if ($branchCount >= $plan->branch_limit) {
                    return response()->json([
                        'error' => "Plan Limit Receeded: Your subscription plan tier ('{$plan->name}') caps physical branches at {$plan->branch_limit} locations."
                    ], 403);
                }
            }
        }

        $branch = Branch::create([
            'shop_id' => auth()->user()->shop_id,
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
        ]);

        // Auto initialize branch stocks for existing product variants
        $variants = \App\Models\ProductVariant::whereHas('product')->get(); // Grabs all variants under current scope context
        foreach ($variants as $variant) {
            \App\Models\BranchStock::create([
                'shop_id'            => auth()->user()->shop_id,
                'branch_id'          => $branch->id,
                'product_variant_id' => $variant->id,
                'quantity'           => 0,
                'low_stock_alert'    => 10,
            ]);
        }

        return response()->json($branch);
    }

    public function updateBranch(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $data = $request->validate([
            'name'    => 'sometimes|string|max:255',
            'phone'   => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $branch->update($data);
        return response()->json($branch);
    }

    public function deleteBranch($id)
    {
        $branch = Branch::findOrFail($id);
        // Do not allow deleting the last branch
        if (Branch::count() <= 1) {
            return response()->json(['error' => 'You must keep at least one default branch.'], 400);
        }
        $branch->delete();
        return response()->json(['message' => 'Branch deleted successfully.']);
    }

    // === SETTINGS KEY-VALUE REGISTRY ===
    public function getSettings()
    {
        $settings = Setting::all()->pluck('value', 'key');
        
        // Return defaults if empty
        $defaults = [
            'currency_code'    => 'USD',
            'currency_symbol'  => '$',
            'tax_percentage'   => '0.00',
            'invoice_header'   => 'Thank you for shopping with us!',
            'invoice_footer'   => 'Please come again.',
            'low_stock_limit'  => '10'
        ];

        foreach ($defaults as $key => $val) {
            if (!isset($settings[$key])) {
                $settings[$key] = $val;
            }
        }

        return response()->json($settings);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'settings' => 'required|array'
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->settings as $key => $val) {
                Setting::updateOrCreate(
                    ['shop_id' => auth()->user()->shop_id, 'key' => $key],
                    ['value' => $val]
                );
            }
        });

        return response()->json(['message' => 'Shop settings updated successfully.']);
    }
}
