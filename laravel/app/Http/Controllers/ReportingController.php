<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Purchase;
use App\Models\BranchStock;
use App\Models\StockMovement;
use App\Models\Expense;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    /**
     * Sales Report (Daily, Weekly, Monthly, Yearly aggregates)
     */
    public function salesReport(Request $request)
    {
        $request->validate([
            'group_by'   => 'required|in:day,week,month,year',
            'branch_id'  => 'nullable|exists:branches,id',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ]);

        $start = $request->start_date ?? now()->startOfMonth()->toDateString();
        $end = $request->end_date ?? now()->endOfDay()->toDateString();

        $query = Sale::where('hold_status', 'completed')
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Group by date functions based on database driver (SQLite for tests, MySQL/MariaDB for dev/prod)
        $driver = DB::getDriverName();
        $groupBy = $request->group_by;

        if ($driver === 'sqlite') {
            if ($groupBy === 'day') {
                $query->select(DB::raw("strftime('%Y-%m-%d', created_at) as period"), DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as total_orders'));
            } elseif ($groupBy === 'week') {
                $query->select(DB::raw("strftime('%Y-%W', created_at) as period"), DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as total_orders'));
            } elseif ($groupBy === 'month') {
                $query->select(DB::raw("strftime('%Y-%m', created_at) as period"), DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as total_orders'));
            } else {
                $query->select(DB::raw("strftime('%Y', created_at) as period"), DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as total_orders'));
            }
        } else {
            if ($groupBy === 'day') {
                $query->select(DB::raw('DATE(created_at) as period'), DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as total_orders'));
            } elseif ($groupBy === 'week') {
                $query->select(DB::raw('YEARWEEK(created_at) as period'), DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as total_orders'));
            } elseif ($groupBy === 'month') {
                $query->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'), DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as total_orders'));
            } else {
                $query->select(DB::raw('YEAR(created_at) as period'), DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as total_orders'));
            }
        }

        $report = $query->groupBy('period')->orderBy('period', 'asc')->get();

        return response()->json($report);
    }

    /**
     * Inventory Reports (Current Stock levels and movement logs)
     */
    public function inventoryReport(Request $request)
    {
        $branchId = $request->input('branch_id');
        
        $stocks = BranchStock::with(['branch', 'productVariant.product']);
        if ($branchId) {
            $stocks->where('branch_id', $branchId);
        }

        $stockData = $stocks->get();

        $lowStocks = (clone $stocks)->whereRaw('quantity <= low_stock_alert')->get();

        $movements = StockMovement::with(['branch', 'productVariant.product'])
            ->orderBy('created_at', 'desc')
            ->limit(100);
            
        if ($branchId) {
            $movements->where('branch_id', $branchId);
        }
        
        $movementData = $movements->get();

        return response()->json([
            'stock_status'    => $stockData,
            'low_stock_items' => $lowStocks,
            'recent_movements'=> $movementData
        ]);
    }

    /**
     * Financial / Expense Report
     */
    public function financialReport(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'branch_id'  => 'nullable|exists:branches,id',
        ]);

        $start = $request->start_date ?? now()->startOfMonth()->toDateString();
        $end = $request->end_date ?? now()->endOfDay()->toDateString();

        $expenseQuery = Expense::whereBetween('date', [$start, $end]);
        $taxQuery = Sale::where('hold_status', 'completed')
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);

        if ($request->filled('branch_id')) {
            $expenseQuery->where('branch_id', $request->branch_id);
            $taxQuery->where('branch_id', $request->branch_id);
        }

        // Expenses grouped by Category
        $expenses = $expenseQuery->select('category', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category')
            ->get();

        // Tax collected
        $taxCollected = $taxQuery->sum('tax_amount');

        return response()->json([
            'expenses_by_category' => $expenses,
            'tax_collected'        => $taxCollected
        ]);
    }

    /**
     * Customer Report (Customer rankings and purchases summary)
     */
    public function customerReport()
    {
        // Top Customers by total purchase amount
        $topCustomers = Customer::select('customers.*', DB::raw('SUM(sales.total_amount) as total_spent'))
            ->leftJoin('sales', 'customers.id', '=', 'sales.customer_id')
            ->where('sales.hold_status', 'completed')
            ->groupBy('customers.id')
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'top_customers' => $topCustomers
        ]);
    }
}
