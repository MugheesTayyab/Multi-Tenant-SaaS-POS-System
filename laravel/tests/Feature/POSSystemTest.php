<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Models\User;
use App\Models\CashRegister;
use App\Models\Sale;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class POSSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset Spatie cache
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Seed SaaS Subscription Plans
        \DB::table('subscriptions')->insert([
            ['id' => 1, 'name' => 'Free', 'monthly_price' => 0.00, 'user_limit' => 2, 'product_limit' => 50, 'branch_limit' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Basic', 'monthly_price' => 29.99, 'user_limit' => 5, 'product_limit' => 500, 'branch_limit' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Premium', 'monthly_price' => 79.99, 'user_limit' => 15, 'product_limit' => 2000, 'branch_limit' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Seed Spatie roles/permissions globally
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        
        $permissions = ['manage-all-shops', 'approve-shops', 'create-sales', 'manage-inventory', 'view-reports'];
        foreach ($permissions as $p) {
            Permission::findOrCreate($p, 'web');
        }

        $superAdminRole = Role::findOrCreate('Super Admin', 'web');
        $shopOwnerRole = Role::findOrCreate('Shop Owner', 'web');
        $cashierRole = Role::findOrCreate('Cashier', 'web');

        $superAdminRole->givePermissionTo(['manage-all-shops', 'approve-shops']);
        $shopOwnerRole->givePermissionTo(['create-sales', 'manage-inventory', 'view-reports']);
        $cashierRole->givePermissionTo(['create-sales']);
    }

    /**
     * Test Landlord security routes.
     */
    public function test_landlord_routes_require_super_admin_authorization()
    {
        // Create a shop owner user
        $shop = Shop::create([
            'name' => 'Test Shop 1',
            'owner_name' => 'Owner 1',
            'email' => 'owner1@shop.com',
            'phone' => '123',
            'address' => 'Addr 1',
            'subscription_id' => 1,
            'status' => 'approved',
        ]);
        
        $owner = User::create([
            'shop_id' => $shop->id,
            'name' => 'Owner 1',
            'email' => 'owner1@shop.com',
            'password' => bcrypt('password'),
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($shop->id);
        $owner->assignRole('Shop Owner');

        // Login as Owner 1 and attempt to view all shops (which is a Super Admin action)
        $response = $this->actingAs($owner)->getJson('/saas/admin/shops');
        
        // This should fail with 403 Forbidden!
        $response->assertStatus(403);
    }

    /**
     * Test Multi-Tenant logic: Shop A cannot view or checkout products of Shop B.
     */
    public function test_multi_tenant_lookup_and_checkout_isolation()
    {
        // 1. Create Shop A
        $shopA = Shop::create([
            'name' => 'Shop A', 'owner_name' => 'Owner A', 'email' => 'a@shop.com',
            'phone' => '123', 'address' => 'Addr A', 'subscription_id' => 1, 'status' => 'approved'
        ]);
        $ownerA = User::create([
            'shop_id' => $shopA->id, 'name' => 'Owner A', 'email' => 'a@shop.com', 'password' => bcrypt('password')
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($shopA->id);
        $ownerA->assignRole('Shop Owner');

        // Create Branch A
        $branchA = Branch::create(['shop_id' => $shopA->id, 'name' => 'Branch A']);
        $ownerA->update(['branch_id' => $branchA->id]);

        // Create Product and Variant for Shop A
        $catA = Category::create(['shop_id' => $shopA->id, 'name' => 'Cat A']);
        $unitA = Unit::create(['shop_id' => $shopA->id, 'name' => 'Pcs', 'short_name' => 'pcs']);
        $prodA = Product::create([
            'shop_id' => $shopA->id, 'category_id' => $catA->id, 'unit_id' => $unitA->id,
            'name' => 'Product A', 'has_variants' => false
        ]);
        $varA = ProductVariant::create([
            'product_id' => $prodA->id, 'variant_name' => 'Standard', 'sku' => 'SKU-A',
            'barcode' => 'BAR-A', 'cost_price' => 1.00, 'selling_price' => 2.00
        ]);
        
        // Initialize stock for Shop A
        BranchStock::create([
            'shop_id' => $shopA->id, 'branch_id' => $branchA->id,
            'product_variant_id' => $varA->id, 'quantity' => 10, 'low_stock_alert' => 2
        ]);

        // 2. Create Shop B
        $shopB = Shop::create([
            'name' => 'Shop B', 'owner_name' => 'Owner B', 'email' => 'b@shop.com',
            'phone' => '123', 'address' => 'Addr B', 'subscription_id' => 1, 'status' => 'approved'
        ]);
        $ownerB = User::create([
            'shop_id' => $shopB->id, 'name' => 'Owner B', 'email' => 'b@shop.com', 'password' => bcrypt('password')
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($shopB->id);
        $ownerB->assignRole('Shop Owner');

        $branchB = Branch::create(['shop_id' => $shopB->id, 'name' => 'Branch B']);
        $ownerB->update(['branch_id' => $branchB->id]);

        $catB = Category::create(['shop_id' => $shopB->id, 'name' => 'Cat B']);
        $unitB = Unit::create(['shop_id' => $shopB->id, 'name' => 'Pcs', 'short_name' => 'pcs']);
        $prodB = Product::create([
            'shop_id' => $shopB->id, 'category_id' => $catB->id, 'unit_id' => $unitB->id,
            'name' => 'Product B', 'has_variants' => false
        ]);
        $varB = ProductVariant::create([
            'product_id' => $prodB->id, 'variant_name' => 'Standard', 'sku' => 'SKU-B',
            'barcode' => 'BAR-B', 'cost_price' => 5.00, 'selling_price' => 10.00
        ]);

        // 3. Perform Test under Owner A context
        // Lookup Variant A (should succeed)
        $response = $this->actingAs($ownerA)->postJson('/api/pos/lookup', ['query' => 'BAR-A']);
        $response->assertStatus(200);
        $response->assertJsonFragment(['sku' => 'SKU-A']);

        // Lookup Variant B (should FAIL with 404, not leak Shop B's product)
        $response = $this->actingAs($ownerA)->postJson('/api/pos/lookup', ['query' => 'BAR-B']);
        $response->assertStatus(404);

        // Open register for Owner A
        $this->actingAs($ownerA)->postJson('/api/financial/register/open', [
            'branch_id' => $branchA->id,
            'opening_balance' => 100.00
        ]);

        // Try checking out Variant B under Owner A (should FAIL/Prevented)
        $response = $this->actingAs($ownerA)->postJson('/api/pos/checkout', [
            'branch_id' => $branchA->id,
            'hold_status' => 'completed',
            'items' => [
                ['product_variant_id' => $varB->id, 'quantity' => 1, 'unit_price' => 10.00]
            ],
            'payments' => [
                ['amount' => 10.00, 'payment_method' => 'Cash']
            ]
        ]);
        // This checkout must fail because the variant doesn't belong to Shop A!
        $response->assertStatus(403);
    }

    /**
     * Test Spatie team role permissions are correct when creating employees.
     */
    public function test_employee_creation_and_role_permissions()
    {
        $shop = Shop::create([
            'name' => 'Shop A', 'owner_name' => 'Owner A', 'email' => 'a@shop.com',
            'phone' => '123', 'address' => 'Addr A', 'subscription_id' => 1, 'status' => 'approved'
        ]);
        $owner = User::create([
            'shop_id' => $shop->id, 'name' => 'Owner A', 'email' => 'a@shop.com', 'password' => bcrypt('password')
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($shop->id);
        $owner->assignRole('Shop Owner');

        $branch = Branch::create(['shop_id' => $shop->id, 'name' => 'Branch A']);

        // Create a cashier employee using EmployeeController
        $response = $this->actingAs($owner)->postJson('/api/employees', [
            'name' => 'Cashier Bob',
            'email' => 'bob@shop.com',
            'password' => 'bob123',
            'branch_id' => $branch->id,
            'role' => 'Cashier'
        ]);
        $response->assertStatus(200);

        $bob = User::where('email', 'bob@shop.com')->first();
        $this->assertNotNull($bob);

        // Verify Bob has the Cashier role under Shop's team context
        app(PermissionRegistrar::class)->setPermissionsTeamId($shop->id);
        $this->assertTrue($bob->hasRole('Cashier'));

        // Verify Bob has 'create-sales' permission but NOT 'view-reports'
        $this->assertTrue($bob->hasPermissionTo('create-sales'));
        $this->assertFalse($bob->hasPermissionTo('view-reports'));
    }

    /**
     * Test Cash Register expected cash calculation with Split payments.
     */
    public function test_cash_register_close_variance_with_split_payments()
    {
        $shop = Shop::create([
            'name' => 'Shop A', 'owner_name' => 'Owner A', 'email' => 'a@shop.com',
            'phone' => '123', 'address' => 'Addr A', 'subscription_id' => 1, 'status' => 'approved'
        ]);
        $owner = User::create([
            'shop_id' => $shop->id, 'name' => 'Owner A', 'email' => 'a@shop.com', 'password' => bcrypt('password')
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($shop->id);
        $owner->assignRole('Shop Owner');

        $branch = Branch::create(['shop_id' => $shop->id, 'name' => 'Branch A']);
        $owner->update(['branch_id' => $branch->id]);

        $cat = Category::create(['shop_id' => $shop->id, 'name' => 'Cat A']);
        $unit = Unit::create(['shop_id' => $shop->id, 'name' => 'Pcs', 'short_name' => 'pcs']);
        $prod = Product::create([
            'shop_id' => $shop->id, 'category_id' => $cat->id, 'unit_id' => $unit->id,
            'name' => 'Product A', 'has_variants' => false
        ]);
        $var = ProductVariant::create([
            'product_id' => $prod->id, 'variant_name' => 'Standard', 'sku' => 'SKU-A',
            'barcode' => 'BAR-A', 'cost_price' => 1.00, 'selling_price' => 10.00
        ]);

        BranchStock::create([
            'shop_id' => $shop->id, 'branch_id' => $branch->id,
            'product_variant_id' => $var->id, 'quantity' => 10, 'low_stock_alert' => 2
        ]);

        // Open register
        $this->actingAs($owner)->postJson('/api/financial/register/open', [
            'branch_id' => $branch->id,
            'opening_balance' => 150.00
        ]);

        // Checkout with split payment: $4.00 cash, $6.00 card
        $this->actingAs($owner)->postJson('/api/pos/checkout', [
            'branch_id' => $branch->id,
            'hold_status' => 'completed',
            'items' => [
                ['product_variant_id' => $var->id, 'quantity' => 1, 'unit_price' => 10.00]
            ],
            'payments' => [
                ['amount' => 4.00, 'payment_method' => 'Cash'],
                ['amount' => 6.00, 'payment_method' => 'Card']
            ]
        ]);

        // Close register
        $response = $this->actingAs($owner)->postJson('/api/financial/register/close', [
            'closing_balance' => 154.00
        ]);
        $response->assertStatus(200);

        // Expected balance should be 150.00 (opening) + 4.00 (cash portion of split) = 154.00
        $response->assertJsonFragment([
            'expected_balance' => 154,
            'variance' => 0
        ]);
    }

    /**
     * Test Report endpoints logic.
     */
    public function test_reports_endpoints()
    {
        $shop = Shop::create([
            'name' => 'Shop A', 'owner_name' => 'Owner A', 'email' => 'a@shop.com',
            'phone' => '123', 'address' => 'Addr A', 'subscription_id' => 1, 'status' => 'approved'
        ]);
        $owner = User::create([
            'shop_id' => $shop->id, 'name' => 'Owner A', 'email' => 'a@shop.com', 'password' => bcrypt('password')
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($shop->id);
        $owner->assignRole('Shop Owner');

        // Seed some sale data to report on
        $branch = Branch::create(['shop_id' => $shop->id, 'name' => 'Branch A']);
        $cat = Category::create(['shop_id' => $shop->id, 'name' => 'Cat A']);
        $unit = Unit::create(['shop_id' => $shop->id, 'name' => 'Pcs', 'short_name' => 'pcs']);
        $prod = Product::create([
            'shop_id' => $shop->id, 'category_id' => $cat->id, 'unit_id' => $unit->id,
            'name' => 'Product A', 'has_variants' => false
        ]);
        $var = ProductVariant::create([
            'product_id' => $prod->id, 'variant_name' => 'Standard', 'sku' => 'SKU-A',
            'barcode' => 'BAR-A', 'cost_price' => 1.00, 'selling_price' => 10.00
        ]);

        $sale = Sale::create([
            'shop_id' => $shop->id,
            'branch_id' => $branch->id,
            'user_id' => $owner->id,
            'invoice_number' => 'INV-TEST-1',
            'total_amount' => 10.00,
            'hold_status' => 'completed',
        ]);

        // Hit the reports routes
        $response = $this->actingAs($owner)->getJson('/api/reports/sales?group_by=day');
        $response->assertStatus(200);

        $response = $this->actingAs($owner)->getJson('/api/reports/inventory');
        $response->assertStatus(200);

        $response = $this->actingAs($owner)->getJson('/api/reports/financial');
        $response->assertStatus(200);

        $response = $this->actingAs($owner)->getJson('/api/reports/customers');
        $response->assertStatus(200);
    }
}
