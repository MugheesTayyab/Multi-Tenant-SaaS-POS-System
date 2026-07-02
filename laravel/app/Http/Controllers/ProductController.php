<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\BranchStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // === CATEGORIES ===
    public function listCategories()
    {
        return response()->json(Category::all());
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $category = Category::create($data);
        return response()->json($category);
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted.']);
    }

    // === BRANDS ===
    public function listBrands()
    {
        return response()->json(Brand::all());
    }

    public function storeBrand(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $brand = Brand::create($data);
        return response()->json($brand);
    }

    public function deleteBrand($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();
        return response()->json(['message' => 'Brand deleted.']);
    }

    // === UNITS ===
    public function listUnits()
    {
        return response()->json(Unit::all());
    }

    public function storeUnit(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'short_name' => 'required|string|max:10'
        ]);
        $unit = Unit::create($data);
        return response()->json($unit);
    }

    public function deleteUnit($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();
        return response()->json(['message' => 'Unit deleted.']);
    }

    // === PRODUCTS ===
    public function listProducts()
    {
        // Load products with their category, brand, unit, and variants
        $products = Product::with(['category', 'brand', 'unit', 'variants.branchStocks.branch'])->get();
        return response()->json($products);
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'brand_id'     => 'nullable|exists:brands,id',
            'unit_id'      => 'required|exists:units,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'has_variants' => 'required|boolean',
            'variants'     => 'required|array|min:1',
            'variants.*.variant_name' => 'required|string|max:255',
            'variants.*.sku'          => 'required|string|unique:product_variants,sku',
            'variants.*.barcode'      => 'required|string|unique:product_variants,barcode',
            'variants.*.cost_price'   => 'required|numeric|min:0',
            'variants.*.selling_price'=> 'required|numeric|min:0',
            'variants.*.tax_percentage'=> 'sometimes|numeric|min:0|max:100',
            'variants.*.discount'      => 'sometimes|numeric|min:0',
        ]);

        $user = auth()->user();
        $shop = $user->shop;
        
        // SUBSCRIPTION THRESHOLD CHECKS
        if ($shop) {
            $plan = $shop->subscription;
            if ($plan) {
                $currentProductCount = Product::count();
                if ($currentProductCount >= $plan->product_limit) {
                    return response()->json([
                        'error' => "Plan Limit Receeded: Your subscription tier ('{$plan->name}') caps catalog products at {$plan->product_limit} items."
                    ], 403);
                }
            }
        }

        $product = DB::transaction(function () use ($request) {
            // 1. Create parent product registry
            $product = Product::create([
                'category_id'  => $request->category_id,
                'brand_id'     => $request->brand_id,
                'unit_id'      => $request->unit_id,
                'name'         => $request->name,
                'description'  => $request->description,
                'has_variants' => $request->has_variants,
                'status'       => 'active',
            ]);

            // 2. Create child variants
            foreach ($request->variants as $variantData) {
                $variant = $product->variants()->create([
                    'variant_name' => $variantData['variant_name'],
                    'sku'          => $variantData['sku'],
                    'barcode'      => $variantData['barcode'],
                    'cost_price'   => $variantData['cost_price'],
                    'selling_price'=> $variantData['selling_price'],
                    'tax_percentage'=> $variantData['tax_percentage'] ?? 0.00,
                    'discount'     => $variantData['discount'] ?? 0.00,
                ]);

                // Auto initialize stock records with 0 count for existing branches
                $branches = auth()->user()->shop->id 
                    ? \App\Models\Branch::where('shop_id', auth()->user()->shop_id)->get() 
                    : [];
                foreach ($branches as $branch) {
                    BranchStock::create([
                        'shop_id'            => auth()->user()->shop_id,
                        'branch_id'          => $branch->id,
                        'product_variant_id' => $variant->id,
                        'quantity'           => 0,
                        'low_stock_alert'    => 10,
                    ]);
                }
            }

            return $product;
        });

        return response()->json($product->load('variants'));
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product catalog item deleted.']);
    }

    public function importCSV(Request $request)
    {
        $request->validate([
            'csv_data' => 'required|array'
        ]);

        // Mock Import of products
        $importedCount = 0;
        foreach ($request->csv_data as $row) {
            // Process CSV row
            $importedCount++;
        }

        return response()->json(['message' => "Successfully processed import file with {$importedCount} entries."]);
    }
}
