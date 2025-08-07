<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\{Member, Account, Category, Transaction};

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

        $fromAccount = Account::where('user_id', $user_id)->findOrFail($data['from_account_id']);
        $toAccount = Account::where('user_id', $user_id)->findOrFail($data['to_account_id']);

        if ($fromAccount->balance < $data['amount']) {
            throw ValidationException::withMessages(['amount' => 'Saldo akun asal tidak cukup']);
        }

        // Ambil atau buat kategori Transfer Out dan Transfer In hanya sekali
        $transferOutCategory = Category::firstOrCreate(
            ['user_id' => $user_id, 'name' => 'Transfer Out', 'type' => 'expense']
        );
        $transferInCategory = Category::firstOrCreate(
            ['user_id' => $user_id, 'name' => 'Transfer In', 'type' => 'income']
        );

        DB::beginTransaction();

        try {
            // Kurangi saldo akun asal
            $fromAccount->decrement('balance', $data['amount']);

            // Tambah saldo akun tujuan
            $toAccount->increment('balance', $data['amount']);

            // Simpan transaksi transfer keluar (expense)
            Transaction::create([
                'user_id' => $user_id,
                'member_id' => $data['member_id'],
                'account_id' => $fromAccount->id,
                'category_id' => $transferOutCategory->id,
                'amount' => $data['amount'],
                'type' => 'transfer',
                'date' => $data['date'],
                'description' => $data['description'] ?? 'Transfer ke akun lain',
            ]);

            // Simpan transaksi transfer masuk (income)
            Transaction::create([
                'user_id' => $user_id,
                'member_id' => $data['member_id'],
                'account_id' => $toAccount->id,
                'category_id' => $transferInCategory->id,
                'amount' => $data['amount'],
                'type' => 'transfer',
                'date' => $data['date'],
                'description' => $data['description'] ?? 'Transfer dari akun lain',
            ]);

            DB::commit();

            return response()->json(['message' => 'Transfer berhasil'], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
