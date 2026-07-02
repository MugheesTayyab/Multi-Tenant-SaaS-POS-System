<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // 2. Brands
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // 3. Units
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('short_name');
            $table->timestamps();
        });

        // 4. Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('has_variants')->default(false);
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // 5. Product Variants
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('variant_name'); // e.g. 'Large - Navy Blue' or 'Standard'
            $table->string('sku')->unique();
            $table->string('barcode')->unique()->index();
            $table->decimal('cost_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 6. Branch Stocks
        Schema::create('branch_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_alert')->default(10);
            $table->timestamps();
            $table->unique(['branch_id', 'product_variant_id']);
        });

        // 7. Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('customer_group')->default('General');
            $table->integer('loyalty_points')->default(0);
            $table->decimal('balance', 10, 2)->default(0.00); // Dynamic ledger balance
            $table->timestamps();
        });

        // 8. Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->decimal('balance', 10, 2)->default(0.00); // Running accounts payable balance
            $table->timestamps();
        });

        // 9. Purchases (Stock replenishment orders)
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The logged-in employee placing it
            $table->string('purchase_number')->unique();
            $table->string('status')->default('pending'); // pending, approved, received
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 10. Purchase Items (Replenishment lines)
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('cost_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        // 11. Sales (Transaction Headers)
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Cashier
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('invoice_number')->unique()->index();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('payment_status')->default('Paid'); // Paid, Partial, Refunded, Unpaid
            $table->string('payment_method')->default('Cash'); // Cash, Card, Bank Transfer, Split
            $table->string('hold_status')->default('completed'); // active (on hold), completed
            $table->timestamps();
        });

        // 12. Sale Items (Receipt Details)
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        // 13. Payments (Breakdown records for splits/records)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // Cash, Card, Bank Transfer, Digital Wallet
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });

        // 14. Stock Movements (Immutable audit ledger)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
            $table->integer('quantity'); // Positive for stock-in/adjustment-up, negative for sale/damaged/transfer-out
            $table->string('type'); // Sale, Purchase, Adjustment, Transfer, Damage
            $table->unsignedBigInteger('reference_id')->nullable(); // Polymorphic pointer to order/item
            $table->timestamps();
        });

        // 15. Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('amount', 10, 2);
            $table->string('category'); // e.g. Rent, Utilities, Salaries, Marketing
            $table->text('description')->nullable();
            $table->date('date');
            $table->timestamps();
        });

        // 16. Cash Registers (Cashier Drawer Shift logs)
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('opening_balance', 10, 2)->default(0.00);
            $table->decimal('closing_balance', 10, 2)->nullable();
            $table->string('status')->default('open'); // open, closed
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // 17. Settings (Store config registry)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['shop_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('cash_registers');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('branch_stocks');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
