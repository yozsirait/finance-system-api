<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Member, Account, Category, BudgetCategory, Transaction, RecurringTransaction, SavingTarget};

class BackupController extends Controller
{
    public function export()
    {
        $user_id = Auth::id();

        $data = [
            'members' => Member::where('user_id', $user_id)->get(),
            'accounts' => Account::where('user_id', $user_id)->get(),
            'categories' => Category::where('user_id', $user_id)->get(),
            'budget_categories' => BudgetCategory::where('user_id', $user_id)->get(),
            'transactions' => Transaction::where('user_id', $user_id)->get(),
            'recurring_transactions' => RecurringTransaction::where('user_id', $user_id)->get(),
            'saving_targets' => SavingTarget::where('user_id', $user_id)->get(),
        ];

        return response()->json($data);
    }

    public function import(Request $request)
    {
        $user_id = Auth::id();

        $data = $request->validate([
            'members' => 'array',
            'accounts' => 'array',
            'categories' => 'array',
            'budget_categories' => 'array',
            'transactions' => 'array',
            'recurring_transactions' => 'array',
            'saving_targets' => 'array',
        ]);

        foreach ($data['members'] as $member) {
            Member::updateOrCreate(
                ['id' => $member['id'], 'user_id' => $user_id],
                array_merge($member, ['user_id' => $user_id])
            );
        }

        foreach ($data['accounts'] as $account) {
            Account::updateOrCreate(
                ['id' => $account['id'], 'user_id' => $user_id],
                array_merge($account, ['user_id' => $user_id])
            );
        }

        foreach ($data['categories'] as $category) {
            Category::updateOrCreate(
                ['id' => $category['id'], 'user_id' => $user_id],
                array_merge($category, ['user_id' => $user_id])
            );
        }

        foreach ($data['budget_categories'] as $budget) {
            BudgetCategory::updateOrCreate(
                ['id' => $budget['id'], 'user_id' => $user_id],
                array_merge($budget, ['user_id' => $user_id])
            );
        }

        foreach ($data['transactions'] as $transaction) {
            Transaction::updateOrCreate(
                ['id' => $transaction['id'], 'user_id' => $user_id],
                array_merge($transaction, ['user_id' => $user_id])
            );
        }

        foreach ($data['recurring_transactions'] as $recurring) {
            RecurringTransaction::updateOrCreate(
                ['id' => $recurring['id'], 'user_id' => $user_id],
                array_merge($recurring, ['user_id' => $user_id])
            );
        }

        foreach ($data['saving_targets'] as $saving) {
            SavingTarget::updateOrCreate(
                ['id' => $saving['id'], 'user_id' => $user_id],
                array_merge($saving, ['user_id' => $user_id])
            );
        }

        return response()->json(['message' => 'Data berhasil diimport']);
    }
}
