<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Shop;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Expense;
use App\Models\CashRegister;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\StockMovement;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Force Spatie to use NULL (global roles/permissions context) for role definition
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        // 2. Seed SaaS Subscription Plans
        DB::table('subscriptions')->insert([
            ['name' => 'Free', 'monthly_price' => 0.00, 'user_limit' => 2, 'product_limit' => 50, 'branch_limit' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Basic', 'monthly_price' => 29.99, 'user_limit' => 5, 'product_limit' => 500, 'branch_limit' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Premium', 'monthly_price' => 79.99, 'user_limit' => 15, 'product_limit' => 2000, 'branch_limit' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Enterprise', 'monthly_price' => 199.99, 'user_limit' => 999, 'product_limit' => 99999, 'branch_limit' => 99, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Seed Base System Permissions
        $permissions = [
            'manage-all-shops',
            'approve-shops',
            'create-sales',
            'manage-inventory',
            'view-reports'
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 4. Create System Roles
        $superAdminRole = Role::findOrCreate('Super Admin', 'web');
        $shopOwnerRole  = Role::findOrCreate('Shop Owner', 'web');
        $cashierRole    = Role::findOrCreate('Cashier', 'web');

        // Assign capabilities
        $superAdminRole->givePermissionTo(['manage-all-shops', 'approve-shops']);
        $shopOwnerRole->givePermissionTo(['create-sales', 'manage-inventory', 'view-reports']);
        $cashierRole->givePermissionTo(['create-sales']);

        // 5. Create Platform Landlord Master Account (Super Admin)
        $admin = User::create([
            'name' => 'System Super Admin',
            'email' => 'admin@saaspos.com',
            'password' => bcrypt('admin123'),
        ]);
        // Set Spatie Team context to landlord team (0)
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);
        $admin->assignRole($superAdminRole);

        // 6. Create Active Tenant Shop
        $shop = Shop::create([
            'name'            => 'Alpha Grocers',
            'owner_name'       => 'Alice Johnson',
            'email'           => 'owner@alphagrocers.com',
            'phone'           => '555-0199',
            'address'         => '100 Business Parkway, Sector 4',
            'subscription_id' => 3, // Premium plan
            'status'          => 'approved',
        ]);

        // Configure Spatie Team scoping context to this shop
        app(PermissionRegistrar::class)->setPermissionsTeamId($shop->id);

        // 7. Create Shop Owner Account for the Tenant
        $owner = User::create([
            'shop_id'  => $shop->id,
            'name'     => 'Alice Johnson',
            'email'    => 'owner@alphagrocers.com',
            'password' => bcrypt('welcome123'),
        ]);
        $owner->assignRole($shopOwnerRole);

        // 8. Create Branches
        $branch1 = Branch::create([
            'shop_id' => $shop->id,
            'name'    => 'Alpha Grocers - Downtown',
            'phone'   => '555-0101',
            'address' => '42 Market Street, Downtown Core',
        ]);

        $branch2 = Branch::create([
            'shop_id' => $shop->id,
            'name'    => 'Alpha Grocers - East Side',
            'phone'   => '555-0102',
            'address' => '789 Suburban Way, East Side Plaza',
        ]);

        // Assign branch 1 to owner by default
        $owner->update(['branch_id' => $branch1->id]);

        // 9. Categories
        $catBeverages = Category::create(['shop_id' => $shop->id, 'name' => 'Beverages']);
        $catSnacks    = Category::create(['shop_id' => $shop->id, 'name' => 'Snacks & Confectionery']);
        $catDairy     = Category::create(['shop_id' => $shop->id, 'name' => 'Dairy Products']);

        // 10. Brands
        $brandCola = Brand::create(['shop_id' => $shop->id, 'name' => 'Coca-Cola']);
        $brandNest = Brand::create(['shop_id' => $shop->id, 'name' => 'Nestle']);
        $brandLac  = Brand::create(['shop_id' => $shop->id, 'name' => 'Lactalis']);

        // 11. Units
        $unitPcs = Unit::create(['shop_id' => $shop->id, 'name' => 'Pieces', 'short_name' => 'pcs']);
        $unitKg  = Unit::create(['shop_id' => $shop->id, 'name' => 'Kilograms', 'short_name' => 'kg']);

        // 12. Products & Variants
        // Product 1: Coke
        $prodCoke = Product::create([
            'shop_id'     => $shop->id,
            'category_id' => $catBeverages->id,
            'brand_id'    => $brandCola->id,
            'unit_id'     => $unitPcs->id,
            'name'        => 'Classic Coke 330ml Can',
            'description' => 'Cold refreshment soda drink.',
            'has_variants'=> false,
        ]);
        $varCoke = ProductVariant::create([
            'product_id'    => $prodCoke->id,
            'variant_name'  => 'Standard',
            'sku'           => 'COKE-330ML',
            'barcode'       => 'BAR101',
            'cost_price'    => 0.45,
            'selling_price' => 1.20,
            'tax_percentage'=> 5.00,
        ]);

        // Product 2: Nestle KitKat
        $prodKitKat = Product::create([
            'shop_id'     => $shop->id,
            'category_id' => $catSnacks->id,
            'brand_id'    => $brandNest->id,
            'unit_id'     => $unitPcs->id,
            'name'        => 'Nestle KitKat 4-Finger',
            'description' => 'Crisp wafer finger covered in milk chocolate.',
            'has_variants'=> false,
        ]);
        $varKitKat = ProductVariant::create([
            'product_id'    => $prodKitKat->id,
            'variant_name'  => 'Standard',
            'sku'           => 'KITKAT-4F',
            'barcode'       => 'BAR102',
            'cost_price'    => 0.60,
            'selling_price' => 1.50,
            'tax_percentage'=> 5.00,
        ]);

        // Product 3: Cheddar Cheese
        $prodCheese = Product::create([
            'shop_id'     => $shop->id,
            'category_id' => $catDairy->id,
            'brand_id'    => $brandLac->id,
            'unit_id'     => $unitKg->id,
            'name'        => 'Premium Cheddar Block',
            'description' => 'Aged white cheddar block.',
            'has_variants'=> false,
        ]);
        $varCheese = ProductVariant::create([
            'product_id'    => $prodCheese->id,
            'variant_name'  => 'Standard',
            'sku'           => 'CHEDDAR-KG',
            'barcode'       => 'BAR103',
            'cost_price'    => 6.50,
            'selling_price' => 12.00,
            'tax_percentage'=> 8.00,
        ]);

        // 13. Initialize Stock Counts
        BranchStock::create(['shop_id' => $shop->id, 'branch_id' => $branch1->id, 'product_variant_id' => $varCoke->id, 'quantity' => 120, 'low_stock_alert' => 15]);
        BranchStock::create(['shop_id' => $shop->id, 'branch_id' => $branch1->id, 'product_variant_id' => $varKitKat->id, 'quantity' => 85, 'low_stock_alert' => 15]);
        BranchStock::create(['shop_id' => $shop->id, 'branch_id' => $branch1->id, 'product_variant_id' => $varCheese->id, 'quantity' => 20, 'low_stock_alert' => 5]);

        BranchStock::create(['shop_id' => $shop->id, 'branch_id' => $branch2->id, 'product_variant_id' => $varCoke->id, 'quantity' => 50, 'low_stock_alert' => 10]);
        BranchStock::create(['shop_id' => $shop->id, 'branch_id' => $branch2->id, 'product_variant_id' => $varKitKat->id, 'quantity' => 40, 'low_stock_alert' => 10]);
        BranchStock::create(['shop_id' => $shop->id, 'branch_id' => $branch2->id, 'product_variant_id' => $varCheese->id, 'quantity' => 8, 'low_stock_alert' => 2]);

        // 14. Seed Suppliers & Customers
        $supFoods = Supplier::create([
            'shop_id' => $shop->id,
            'name'    => 'Global Foods Distributors',
            'contact_name' => 'Steve Rogers',
            'email'   => 'steve@globalfoods.com',
            'phone'   => '555-3211',
            'balance' => 0.00
        ]);

        $custJohn = Customer::create([
            'shop_id'        => $shop->id,
            'name'           => 'John Doe',
            'email'          => 'john@gmail.com',
            'phone'          => '555-8888',
            'customer_group' => 'General',
            'loyalty_points' => 35,
            'balance'        => 0.00
        ]);

        // 15. Shift Drawer open register log
        CashRegister::create([
            'shop_id'         => $shop->id,
            'branch_id'       => $branch1->id,
            'user_id'         => $owner->id,
            'opening_balance' => 150.00,
            'status'          => 'open',
            'opened_at'       => now()->startOfDay(),
        ]);

        // 16. Seed Expenses
        Expense::create([
            'shop_id'     => $shop->id,
            'branch_id'   => $branch1->id,
            'name'        => 'June Rent Space',
            'amount'      => 450.00,
            'category'    => 'Rent',
            'date'        => now()->startOfMonth()->toDateString(),
        ]);

        Expense::create([
            'shop_id'     => $shop->id,
            'branch_id'   => $branch1->id,
            'name'        => 'Electricity Billing',
            'amount'      => 85.50,
            'category'    => 'Utilities',
            'date'        => now()->subDays(5)->toDateString(),
        ]);

        // 17. Seed Mock Sales Transactions
        // Sale 1: Coke purchase
        $sale1 = Sale::create([
            'shop_id'         => $shop->id,
            'branch_id'       => $branch1->id,
            'user_id'         => $owner->id,
            'customer_id'     => $custJohn->id,
            'invoice_number'  => 'INV-10001-SEED',
            'total_amount'    => 25.20,
            'tax_amount'      => 1.20,
            'discount_amount' => 0.00,
            'payment_status'  => 'Paid',
            'payment_method'  => 'Cash',
            'hold_status'      => 'completed',
            'created_at'      => now()->subDays(2),
        ]);
        $sale1->items()->create(['product_variant_id' => $varCoke->id, 'quantity' => 10, 'unit_price' => 1.20, 'tax_amount' => 0.60, 'subtotal' => 12.60]);
        $sale1->items()->create(['product_variant_id' => $varKitKat->id, 'quantity' => 8, 'unit_price' => 1.50, 'tax_amount' => 0.60, 'subtotal' => 12.60]);
        Payment::create(['shop_id' => $shop->id, 'sale_id' => $sale1->id, 'amount' => 25.20, 'payment_method' => 'Cash']);
        StockMovement::create(['shop_id' => $shop->id, 'branch_id' => $branch1->id, 'product_variant_id' => $varCoke->id, 'quantity' => -10, 'type' => 'Sale', 'reference_id' => $sale1->id]);
        StockMovement::create(['shop_id' => $shop->id, 'branch_id' => $branch1->id, 'product_variant_id' => $varKitKat->id, 'quantity' => -8, 'type' => 'Sale', 'reference_id' => $sale1->id]);

        // Sale 2: Cheese purchase
        $sale2 = Sale::create([
            'shop_id'         => $shop->id,
            'branch_id'       => $branch1->id,
            'user_id'         => $owner->id,
            'customer_id'     => $custJohn->id,
            'invoice_number'  => 'INV-10002-SEED',
            'total_amount'    => 38.88,
            'tax_amount'      => 2.88,
            'discount_amount' => 0.00,
            'payment_status'  => 'Paid',
            'payment_method'  => 'Card',
            'hold_status'      => 'completed',
            'created_at'      => now()->subDays(1),
        ]);
        $sale2->items()->create(['product_variant_id' => $varCheese->id, 'quantity' => 3, 'unit_price' => 12.00, 'tax_amount' => 2.88, 'subtotal' => 38.88]);
        Payment::create(['shop_id' => $shop->id, 'sale_id' => $sale2->id, 'amount' => 38.88, 'payment_method' => 'Card']);
        StockMovement::create(['shop_id' => $shop->id, 'branch_id' => $branch1->id, 'product_variant_id' => $varCheese->id, 'quantity' => -3, 'type' => 'Sale', 'reference_id' => $sale2->id]);
    }
}