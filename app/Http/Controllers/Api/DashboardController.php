<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{
    Member,
    Account,
    Category,
    BudgetCategory,
    Transaction,
    RecurringTransaction,
    SavingTarget
};
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user_id = Auth::id();

        // Saldo total semua akun
        $totalSaldo = Account::where('user_id', $user_id)->sum('balance');

        // Pemasukan & Pengeluaran total
        $totalIncome = Transaction::where('user_id', $user_id)->where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('user_id', $user_id)->where('type', 'expense')->sum('amount');

        // Statistik bulanan
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $monthlyIncome = Transaction::where('user_id', $user_id)
            ->where('type', 'income')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $monthlyExpense = Transaction::where('user_id', $user_id)
            ->where('type', 'expense')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Realisasi Budget per Kategori
        $budgetCategories = BudgetCategory::where('user_id', $user_id)->with('category')->get()->map(function ($item) use ($user_id) {
            $realization = Transaction::where('user_id', $user_id)
                ->where('category_id', $item->category_id)
                ->where('type', 'expense')
                ->sum('amount');

            return [
                'category' => $item->category->name,
                'budget' => $item->amount,
                'realization' => $realization
            ];
        });

        // Tabungan (Saving Target)
        $savingTargets = SavingTarget::where('user_id', $user_id)->get()->map(function ($target) {
            $progress = $target->current_amount / $target->target_amount * 100;
            return [
                'name' => $target->name,
                'target_amount' => $target->target_amount,
                'current_amount' => $target->current_amount,
                'progress_percent' => round($progress, 2)
            ];
        });

        // Saldo per akun
        $accounts = Account::where('user_id', $user_id)->get(['name', 'balance']);

        // Jumlah transaksi hari ini
        $dailyTransactionCount = Transaction::where('user_id', $user_id)
            ->whereDate('date', Carbon::today())
            ->count();

        return response()->json([
            'total_balance' => $totalSaldo,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'monthly_income' => $monthlyIncome,
            'monthly_expense' => $monthlyExpense,
            'daily_transaction_count' => $dailyTransactionCount,
            'accounts' => $accounts,
            'budget_categories' => $budgetCategories,
            'saving_targets' => $savingTargets,
        ]);
    }
}
