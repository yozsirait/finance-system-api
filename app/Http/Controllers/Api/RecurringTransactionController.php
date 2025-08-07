<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Member, Account, Category, BudgetCategory, Transaction, RecurringTransaction, SavingTarget};

class RecurringTransactionController extends Controller
{
    public function index()
    {
        return RecurringTransaction::where('user_id', Auth::id())->get();
    }

    public function store(Request $request)
    {
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

    public function update(Request $request, $id)
    {
        $item = RecurringTransaction::where('user_id', Auth::id())->findOrFail($id);
        $item->update($request->all());
        return $item;
    }

    public function destroy($id)
    {
        $item = RecurringTransaction::where('user_id', Auth::id())->findOrFail($id);
        $item->delete();
        return response()->noContent();
    }

    public function runRecurring()
    {
        $user_id = Auth::id();
        $today = now()->toDateString();

        $recurrings = RecurringTransaction::where('user_id', $user_id)
            ->whereDate('next_date', '<=', $today)
            ->get();

        $created = [];

        foreach ($recurrings as $recurring) {
            $transaction = Transaction::create([
                'user_id'     => $user_id,
                'member_id'   => $recurring->member_id,
                'account_id'  => $recurring->account_id,
                'category_id' => $recurring->category_id,
                'type'        => $recurring->type,
                'amount'      => $recurring->amount,
                'description' => $recurring->description,
                'date'        => $today,
            ]);

            // Update next_date berdasarkan recurring_type
            $recurring->next_date = $recurring->calculateNextDate();
            $recurring->save();

            $created[] = $transaction;
        }

        return response()->json([
            'message' => count($created) . ' recurring transactions processed.',
            'transactions' => $created
        ]);
    }
}
