<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Events\BudgetLimitReached;
use Carbon\Carbon;
use App\Models\{Member, Account, Category, BudgetCategory, Transaction};

class TransactionController extends Controller
{
    public function index()
    {
        return Transaction::where('user_id', Auth::id())->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        $data['user_id'] = Auth::id();

        // Cek saldo akun jika expense atau transfer
        $account = Account::where('user_id', $data['user_id'])
            ->where('id', $data['account_id'])
            ->first();

        if (!$account) {
            throw ValidationException::withMessages(['account_id' => 'Akun tidak ditemukan']);
        }

        if (in_array($data['type'], ['expense', 'transfer']) && $data['amount'] > $account->balance) {
            throw ValidationException::withMessages(['amount' => 'Jumlah transaksi melebihi saldo akun']);
        }

        // Buat transaksi
        $transaction = Transaction::create($data);

        // Update saldo akun sesuai tipe transaksi
        $this->updateAccountBalance($account, $transaction->type, $transaction->amount);

        // Budget alert jika tipe expense
        if ($transaction->type === 'expense') {
            $this->checkBudgetAlert($transaction);
        }

        return response()->json($transaction, 201);
    }

    public function show($id)
    {
        return Transaction::where('user_id', Auth::id())->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $trx = Transaction::where('user_id', Auth::id())->findOrFail($id);

        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        $accountOld = Account::where('user_id', Auth::id())->where('id', $trx->account_id)->first();
        $accountNew = Account::where('user_id', Auth::id())->where('id', $data['account_id'])->first();

        if (!$accountOld || !$accountNew) {
            throw ValidationException::withMessages(['account_id' => 'Akun tidak ditemukan']);
        }

        // Revert saldo akun lama
        $this->revertAccountBalance($accountOld, $trx->type, $trx->amount);

        // Cek saldo akun baru jika expense atau transfer
        if (in_array($data['type'], ['expense', 'transfer']) && $data['amount'] > $accountNew->balance) {
            // Kembalikan saldo akun lama ke keadaan semula karena gagal update
            $this->updateAccountBalance($accountOld, $trx->type, $trx->amount);

            throw ValidationException::withMessages(['amount' => 'Jumlah transaksi melebihi saldo akun']);
        }

        // Update data transaksi
        $trx->update($data);

        // Update saldo akun baru sesuai data baru
        $this->updateAccountBalance($accountNew, $trx->type, $trx->amount);

        // Budget alert jika tipe expense
        if ($trx->type === 'expense') {
            $this->checkBudgetAlert($trx);
        }

        return response()->json($trx);
    }

    public function destroy($id)
    {
        $trx = Transaction::where('user_id', Auth::id())->findOrFail($id);

        // Revert saldo akun
        $account = Account::where('user_id', Auth::id())->where('id', $trx->account_id)->first();
        if ($account) {
            $this->revertAccountBalance($account, $trx->type, $trx->amount);
        }

        $trx->delete();
        return response()->noContent();
    }

    // Fungsi bantu update saldo akun
    protected function updateAccountBalance(Account $account, string $type, float $amount)
    {
        if ($type === 'income') {
            $account->increment('balance', $amount);
        } elseif (in_array($type, ['expense', 'transfer'])) {
            $account->decrement('balance', $amount);
        }
    }

    // Fungsi bantu revert saldo akun (misal saat update atau delete)
    protected function revertAccountBalance(Account $account, string $type, float $amount)
    {
        if ($type === 'income') {
            $account->decrement('balance', $amount);
        } elseif (in_array($type, ['expense', 'transfer'])) {
            $account->increment('balance', $amount);
        }
    }

    // Fungsi bantu cek budget alert
    protected function checkBudgetAlert(Transaction $transaction)
    {
        $budget = BudgetCategory::where('user_id', $transaction->user_id)
            ->where('category_id', $transaction->category_id)
            ->first();

        if ($budget) {
            $totalSpent = Transaction::where('user_id', $transaction->user_id)
                ->where('category_id', $transaction->category_id)
                ->where('type', 'expense')
                ->whereYear('date', Carbon::parse($transaction->date)->year)
                ->whereMonth('date', Carbon::parse($transaction->date)->month)
                ->sum('amount');

            $percent = ($totalSpent / $budget->amount) * 100;

            if ($percent >= 90) {
                event(new BudgetLimitReached($budget, $percent));
            }
        }
    }
}
