<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Member, Account, Category, BudgetCategory, Transaction, RecurringTransaction, SavingTarget};

class DashboardController extends Controller
{
    public function index()
    {
        $user_id = Auth::id();

        $totalSaldo = Account::where('user_id', $user_id)->sum('balance');
        $totalIncome = Transaction::where('user_id', $user_id)->where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('user_id', $user_id)->where('type', 'expense')->sum('amount');

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

        return response()->json([
            'total_balance' => $totalSaldo,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'budget_categories' => $budgetCategories,
        ]);
    }
}
