<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Member, Account, Category, BudgetCategory, Transaction, RecurringTransaction, SavingTarget};

class BudgetCategoryController extends Controller
{
    public function index()
    {
        return BudgetCategory::where('user_id', Auth::id())->with('category')->get();
    }

    public function show($id)
    {
        $budget = BudgetCategory::with('category')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json($budget);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric'
        ]);
        $data['user_id'] = Auth::id();
        return BudgetCategory::create($data);
    }

    public function update(Request $request, $id)
    {
        $item = BudgetCategory::where('user_id', Auth::id())->findOrFail($id);
        $item->update($request->only(['category_id', 'amount']));
        return $item;
    }

    public function destroy($id)
    {
        $item = BudgetCategory::where('user_id', Auth::id())->findOrFail($id);
        $item->delete();
        return response()->noContent();
    }
}
