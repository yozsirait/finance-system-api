<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Member, Account, Category, BudgetCategory, Transaction, RecurringTransaction, SavingTarget};

class CategoryController extends Controller
{
    public function index()
    {
        return Category::where('user_id', Auth::id())->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'type' => 'required|in:income,expense'
        ]);
        $data['user_id'] = Auth::id();
        return Category::create($data);
    }

    public function update(Request $request, $id)
    {
        $cat = Category::where('user_id', Auth::id())->findOrFail($id);
        $cat->update($request->only(['name', 'type']));
        return $cat;
    }

    public function destroy($id)
    {
        $cat = Category::where('user_id', Auth::id())->findOrFail($id);
        $cat->delete();
        return response()->noContent();
    }
}
