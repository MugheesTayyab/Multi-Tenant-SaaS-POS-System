<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Purchase;
use App\Models\BranchStock;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Fetch executive summary metrics and widgets.
     */
    public function index(Request $request)
    {
        $branchId = $request->input('branch_id');

        // Base builders scoped by tenant implicitly via BelongsToTenant global query scope
        $salesBuilder = Sale::where('hold_status', 'completed');
        $purchaseBuilder = Purchase::where('status', 'received');
        $stockBuilder = BranchStock::with(['branch', 'productVariant.product']);

        if ($branchId) {
            $salesBuilder->where('branch_id', $branchId);
            $purchaseBuilder->where('branch_id', $branchId);
            $stockBuilder->where('branch_id', $branchId);
        }

        // 1. Daily Sales
        $todayStart = now()->startOfDay()->toDateTimeString();
        $todayEnd = now()->endOfDay()->toDateTimeString();
        $dailySales = (clone $salesBuilder)->whereBetween('created_at', [$todayStart, $todayEnd])->sum('total_amount');

        // 2. Monthly Sales
        $monthStart = now()->startOfMonth()->toDateTimeString();
        $monthEnd = now()->endOfMonth()->toDateTimeString();
        $monthlySales = (clone $salesBuilder)->whereBetween('created_at', [$monthStart, $monthEnd])->sum('total_amount');

        // 3. Purchase Outlays (total received)
        $purchaseOutlays = (clone $purchaseBuilder)->sum('total_amount');

        // 4. Stock Summary (Total quantity)
        $stockQuantity = (clone $stockBuilder)->sum('quantity');

        // 5. Low Stock Alerts
        $lowStockAlerts = (clone $stockBuilder)
            ->whereRaw('quantity <= low_stock_alert')
            ->get();

        // 6. Recent Transactions
        $recentTransactions = (clone $salesBuilder)
            ->with(['customer', 'branch', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 7. Top Selling Products
        $topProducts = SaleItem::select('product_variant_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('sale', function ($query) use ($branchId) {
                $query->where('hold_status', 'completed');
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            })
            ->groupBy('product_variant_id')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->with('productVariant.product')
            ->get();

        return response()->json([
            'daily_sales'         => $dailySales,
            'monthly_sales'       => $monthlySales,
            'purchase_outlays'    => $purchaseOutlays,
            'stock_quantity'      => $stockQuantity,
            'low_stock_alerts'    => $lowStockAlerts,
            'recent_transactions' => $recentTransactions,
            'top_products'        => $topProducts,
        ]);
    }
}
