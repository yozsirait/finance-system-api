<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Member, Account, Category, BudgetCategory, Transaction, RecurringTransaction, SavingTarget};

class MemberController extends Controller
{
    public function index()
    {
        return Auth::user()->members;
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required']);
        return Auth::user()->members()->create($data);
    }

    public function show($id)
    {
        return Member::where('user_id', Auth::id())->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $member = Member::where('user_id', Auth::id())->findOrFail($id);
        $member->update($request->validate(['name' => 'required']));
        return $member;
    }

    public function destroy($id)
    {
        $member = Member::where('user_id', Auth::id())->findOrFail($id);
        $member->delete();
        return response()->noContent();
    }
}
