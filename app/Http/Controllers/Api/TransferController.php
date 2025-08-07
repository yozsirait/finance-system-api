<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Member, Account, Category, BudgetCategory, Transaction, RecurringTransaction, SavingTarget};

class TransferController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'member_id' => 'required|exists:members,id',
            'date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        $user_id = Auth::id();

        // Buat pengeluaran dari akun asal
        Transaction::create([
            'user_id' => $user_id,
            'member_id' => $data['member_id'],
            'account_id' => $data['from_account_id'],
            'category_id' => Category::firstOrCreate([
                'user_id' => $user_id,
                'name' => 'Transfer Out',
                'type' => 'expense'
            ])->id,
            'amount' => $data['amount'],
            'type' => 'transfer',
            'date' => $data['date'],
            'description' => $data['description'] ?? 'Transfer ke akun lain'
        ]);

        // Buat pemasukan ke akun tujuan
        Transaction::create([
            'user_id' => $user_id,
            'member_id' => $data['member_id'],
            'account_id' => $data['to_account_id'],
            'category_id' => Category::firstOrCreate([
                'user_id' => $user_id,
                'name' => 'Transfer In',
                'type' => 'income'
            ])->id,
            'amount' => $data['amount'],
            'type' => 'transfer',
            'date' => $data['date'],
            'description' => $data['description'] ?? 'Transfer dari akun lain'
        ]);

        return response()->json(['message' => 'Transfer berhasil'], 201);
    }
}
