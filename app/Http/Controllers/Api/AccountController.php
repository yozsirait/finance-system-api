<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Member, Account, Category, BudgetCategory, Transaction, RecurringTransaction, SavingTarget};

class AccountController extends Controller
{
    public function index()
    {
        return Account::where('user_id', Auth::id())->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'name' => 'required',
            'balance' => 'numeric',
        ]);
        $data['user_id'] = Auth::id();        
        return Account::create($data);
    }

    public function show($id)
    {
        return Account::where('user_id', Auth::id())->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $acc = Account::where('user_id', Auth::id())->findOrFail($id);
        $acc->update($request->only(['name', 'balance']));
        return $acc;
    }

    public function destroy($id)
    {
        $acc = Account::where('user_id', Auth::id())->findOrFail($id);
        $acc->delete();
        return response()->noContent();
    }
}
