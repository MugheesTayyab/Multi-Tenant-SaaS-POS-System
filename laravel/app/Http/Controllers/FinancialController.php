<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\CashRegister;
use App\Models\Sale;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    // === EXPENSES ===
    public function listExpenses()
    {
        return response()->json(Expense::with('branch')->get());
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'branch_id'   => 'required|exists:branches,id',
            'name'        => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'category'    => 'required|string|max:255',
            'description' => 'nullable|string',
            'date'        => 'required|date',
        ]);

        $expense = Expense::create($data);
        return response()->json($expense);
    }

    public function deleteExpense($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();
        return response()->json(['message' => 'Expense entry removed.']);
    }

    // === CASH REGISTER CONTROL ===
    public function registerStatus(Request $request)
    {
        $branchId = $request->input('branch_id');
        $user = auth()->user();

        $register = CashRegister::where('user_id', $user->id)
            ->where('status', 'open');
            
        if ($branchId) {
            $register->where('branch_id', $branchId);
        }

        $register = $register->first();

        return response()->json([
            'is_open'  => $register ? true : false,
            'register' => $register
        ]);
    }

    public function openRegister(Request $request)
    {
        $request->validate([
            'branch_id'       => 'required|exists:branches,id',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();

        // Ensure there is no already open register for this cashier
        $existing = CashRegister::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return response()->json(['error' => 'You already have an active open cash drawer. Close it first.'], 400);
        }

        $register = CashRegister::create([
            'shop_id'         => $user->shop_id,
            'branch_id'       => $request->branch_id,
            'user_id'         => $user->id,
            'opening_balance' => $request->opening_balance,
            'status'          => 'open',
            'opened_at'       => now(),
        ]);

        return response()->json(['message' => 'Cash register shift opened successfully.', 'register' => $register]);
    }

    public function closeRegister(Request $request)
    {
        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();

        $register = CashRegister::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$register) {
            return response()->json(['error' => 'No active open cash register shift found.'], 404);
        }

        // Calculate expected closing balance: Opening balance + Cash payments
        $cashPayments = \App\Models\Payment::where('payment_method', 'Cash')
            ->whereHas('sale', function ($query) use ($user, $register) {
                $query->where('user_id', $user->id)
                    ->where('branch_id', $register->branch_id)
                    ->where('created_at', '>=', $register->opened_at)
                    ->where('hold_status', 'completed');
            })
            ->sum('amount');

        $expected = $register->opening_balance + $cashPayments;

        $register->update([
            'closing_balance' => $request->closing_balance,
            'status'          => 'closed',
            'closed_at'       => now(),
        ]);

        return response()->json([
            'message'          => 'Register drawer closed successfully.',
            'register'         => $register,
            'expected_balance' => $expected,
            'variance'         => $request->closing_balance - $expected
        ]);
    }

    // === PROFIT & LOSS ===
    public function getPLSummary(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'branch_id'  => 'nullable|exists:branches,id',
        ]);

        $start = $request->start_date ?? now()->startOfMonth()->toDateString();
        $end = $request->end_date ?? now()->endOfDay()->toDateString();

        $salesQuery = Sale::where('hold_status', 'completed')
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);

        $purchasesQuery = Purchase::where('status', 'received')
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);

        $expensesQuery = Expense::whereBetween('date', [$start, $end]);

        if ($request->filled('branch_id')) {
            $salesQuery->where('branch_id', $request->branch_id);
            $purchasesQuery->where('branch_id', $request->branch_id);
            $expensesQuery->where('branch_id', $request->branch_id);
        }

        $grossRevenue = $salesQuery->sum('total_amount');
        $purchaseCosts = $purchasesQuery->sum('total_amount');
        $operationalExpenses = $expensesQuery->sum('amount');
        
        // Calculate cost of goods sold (COGS) mock-up based on sale item costs
        $cogs = 0;
        $sales = $salesQuery->with('items.productVariant')->get();
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $cogs += $item->quantity * ($item->productVariant->cost_price ?? 0.00);
            }
        }

        $grossProfit = $grossRevenue - $cogs;
        $netProfit = $grossProfit - $operationalExpenses;

        return response()->json([
            'gross_revenue'         => $grossRevenue,
            'cost_of_goods_sold'    => $cogs,
            'gross_profit'          => $grossProfit,
            'purchase_outlays'      => $purchaseCosts,
            'operational_expenses'  => $operationalExpenses,
            'net_profit'            => $netProfit,
        ]);
    }
}
