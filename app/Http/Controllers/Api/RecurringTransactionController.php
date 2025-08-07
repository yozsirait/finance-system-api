<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Member, Account, Category, BudgetCategory, Transaction, RecurringTransaction, SavingTarget};

class RecurringTransactionController extends Controller
{
    public function index() {
        return RecurringTransaction::where('user_id', Auth::id())->get();
    }

    public function store(Request $request) {
        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric',
            'recurring_type' => 'required|in:daily,weekly,monthly',
            'next_date' => 'required|date',
            'description' => 'nullable'
        ]);
        $data['user_id'] = Auth::id();
        return RecurringTransaction::create($data);
    }

    public function update(Request $request, $id) {
        $item = RecurringTransaction::where('user_id', Auth::id())->findOrFail($id);
        $item->update($request->all());
        return $item;
    }

    public function destroy($id) {
        $item = RecurringTransaction::where('user_id', Auth::id())->findOrFail($id);
        $item->delete();
        return response()->noContent();
    }
}