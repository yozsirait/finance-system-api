<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Member, Account, Category, BudgetCategory, Transaction, RecurringTransaction, SavingTarget};

class TransactionController extends Controller
{
    public function index() {
        return Transaction::where('user_id', Auth::id())->latest()->get();
    }

    public function store(Request $request) {
        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'description' => 'nullable|string'
        ]);
        $data['user_id'] = Auth::id();
        return Transaction::create($data);
    }

    public function show($id) {
        return Transaction::where('user_id', Auth::id())->findOrFail($id);
    }

    public function update(Request $request, $id) {
        $trx = Transaction::where('user_id', Auth::id())->findOrFail($id);
        $trx->update($request->only(['member_id','account_id','category_id','type','amount','date','description']));
        return $trx;
    }

    public function destroy($id) {
        $trx = Transaction::where('user_id', Auth::id())->findOrFail($id);
        $trx->delete();
        return response()->noContent();
    }
}