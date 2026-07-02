<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SaaS\ShopController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\DashboardController;

// ─── PUBLIC TENANT REGISTRATION & AUTH ───
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::post('/saas/register', [ShopController::class, 'register']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/me', [AuthController::class, 'me']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

// ─── SUPER ADMIN OPERATIONS (LANDLORD ROUTING) ───
Route::middleware(['auth', 'can:manage-all-shops'])->group(function () {
    Route::get('/saas/admin/shops', [ShopController::class, 'index']);
    Route::post('/saas/admin/shops/{id}/approve', [ShopController::class, 'approve']);
    Route::post('/saas/admin/shops/{id}/reject', [ShopController::class, 'reject']);
    Route::post('/saas/admin/shops/{id}/suspend', [ShopController::class, 'suspend']);
    Route::post('/saas/admin/shops/{id}/activate', [ShopController::class, 'activate']);
});

// ─── TENANT SHOP WORKSPACE APIS ───
Route::middleware(['auth'])->group(function () {
    // Dashboard Summary
    Route::get('/api/dashboard', [DashboardController::class, 'index']);

    // Catalog & Products
    Route::get('/api/catalog/categories', [ProductController::class, 'listCategories']);
    Route::post('/api/catalog/categories', [ProductController::class, 'storeCategory']);
    Route::delete('/api/catalog/categories/{id}', [ProductController::class, 'deleteCategory']);

    Route::get('/api/catalog/brands', [ProductController::class, 'listBrands']);
    Route::post('/api/catalog/brands', [ProductController::class, 'storeBrand']);
    Route::delete('/api/catalog/brands/{id}', [ProductController::class, 'deleteBrand']);

    Route::get('/api/catalog/units', [ProductController::class, 'listUnits']);
    Route::post('/api/catalog/units', [ProductController::class, 'storeUnit']);
    Route::delete('/api/catalog/units/{id}', [ProductController::class, 'deleteUnit']);

    Route::get('/api/catalog/products', [ProductController::class, 'listProducts']);
    Route::post('/api/catalog/products', [ProductController::class, 'storeProduct']);
    Route::delete('/api/catalog/products/{id}', [ProductController::class, 'deleteProduct']);
    Route::post('/api/catalog/import', [ProductController::class, 'importCSV']);

    // Inventory Control
    Route::get('/api/inventory/stock', [InventoryController::class, 'listStock']);
    Route::post('/api/inventory/adjust', [InventoryController::class, 'stockAdjustment']);
    Route::post('/api/inventory/transfer', [InventoryController::class, 'stockTransfer']);
    Route::get('/api/inventory/movements', [InventoryController::class, 'movements']);

    // Purchases & Suppliers
    Route::get('/api/purchases', [PurchaseController::class, 'listPurchases']);
    Route::post('/api/purchases', [PurchaseController::class, 'storePurchase']);
    Route::post('/api/purchases/{id}/approve', [PurchaseController::class, 'approvePurchase']);
    Route::post('/api/purchases/{id}/receive', [PurchaseController::class, 'receiveGoods']);
    Route::post('/api/purchases/return', [PurchaseController::class, 'storeReturn']);

    Route::get('/api/suppliers', [SupplierController::class, 'index']);
    Route::post('/api/suppliers', [SupplierController::class, 'store']);
    Route::put('/api/suppliers/{id}', [SupplierController::class, 'update']);
    Route::delete('/api/suppliers/{id}', [SupplierController::class, 'destroy']);
    Route::get('/api/suppliers/{id}/ledger', [SupplierController::class, 'ledger']);

    // POS Screen Transactions
    Route::post('/api/pos/lookup', [POSController::class, 'lookupVariant']);
    Route::post('/api/pos/checkout', [POSController::class, 'checkout']);
    Route::get('/api/pos/holds', [POSController::class, 'listHoldSales']);
    Route::post('/api/pos/holds/{id}/resume', [POSController::class, 'resumeSale']);
    Route::post('/api/pos/sales/{id}/refund', [POSController::class, 'refundSale']);

    // Customer Relationship Management
    Route::get('/api/customers', [CustomerController::class, 'index']);
    Route::post('/api/customers', [CustomerController::class, 'store']);
    Route::put('/api/customers/{id}', [CustomerController::class, 'update']);
    Route::delete('/api/customers/{id}', [CustomerController::class, 'destroy']);
    Route::get('/api/customers/{id}/ledger', [CustomerController::class, 'ledger']);

    // Employees & Human Resources
    Route::get('/api/employees', [EmployeeController::class, 'index']);
    Route::post('/api/employees', [EmployeeController::class, 'store']);
    Route::put('/api/employees/{id}', [EmployeeController::class, 'update']);
    Route::delete('/api/employees/{id}', [EmployeeController::class, 'destroy']);
    Route::get('/api/employees/roles', [EmployeeController::class, 'roles']);

    // Financial shift and drawers
    Route::get('/api/financial/expenses', [FinancialController::class, 'listExpenses']);
    Route::post('/api/financial/expenses', [FinancialController::class, 'storeExpense']);
    Route::delete('/api/financial/expenses/{id}', [FinancialController::class, 'deleteExpense']);
    Route::get('/api/financial/register/status', [FinancialController::class, 'registerStatus']);
    Route::post('/api/financial/register/open', [FinancialController::class, 'openRegister']);
    Route::post('/api/financial/register/close', [FinancialController::class, 'closeRegister']);
    Route::get('/api/financial/pl', [FinancialController::class, 'getPLSummary']);

    // Settings
    Route::get('/api/settings/branches', [SettingsController::class, 'listBranches']);
    Route::post('/api/settings/branches', [SettingsController::class, 'storeBranch']);
    Route::put('/api/settings/branches/{id}', [SettingsController::class, 'updateBranch']);
    Route::delete('/api/settings/branches/{id}', [SettingsController::class, 'deleteBranch']);
    Route::get('/api/settings', [SettingsController::class, 'getSettings']);
    Route::post('/api/settings', [SettingsController::class, 'updateSettings']);

    // Analytical Reports
    Route::get('/api/reports/sales', [ReportingController::class, 'salesReport']);
    Route::get('/api/reports/inventory', [ReportingController::class, 'inventoryReport']);
    Route::get('/api/reports/financial', [ReportingController::class, 'financialReport']);
    Route::get('/api/reports/customers', [ReportingController::class, 'customerReport']);
});