<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Member, Account, Category, BudgetCategory, Transaction, RecurringTransaction, SavingTarget};

class SavingTargetController extends Controller
{
    public function index() {
        return SavingTarget::where('user_id', Auth::id())->get();
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required',
            'target_amount' => 'required|numeric',
            'saved_amount' => 'nullable|numeric',
            'due_date' => 'nullable|date'
        ]);
        $data['user_id'] = Auth::id();
        return SavingTarget::create($data);
    }

    public function update(Request $request, $id) {
        $target = SavingTarget::where('user_id', Auth::id())->findOrFail($id);
        $target->update($request->only(['name','target_amount','saved_amount','due_date']));
        return $target;
    }

    public function destroy($id) {
        $target = SavingTarget::where('user_id', Auth::id())->findOrFail($id);
        $target->delete();
        return response()->noContent();
    }
}