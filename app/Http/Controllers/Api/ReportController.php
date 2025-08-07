<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Laporan Bulanan
    public function monthly(Request $request)
    {
        $user_id = Auth::id();

        $year = $request->query('year', date('Y'));
        $month = $request->query('month', date('m'));
        $member_id = $request->query('member_id');
        $account_id = $request->query('account_id');
        $category_id = $request->query('category_id');
        $type = $request->query('type'); // income, expense, transfer

        $query = Transaction::where('user_id', $user_id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        if ($member_id) {
            $query->where('member_id', $member_id);
        }
        if ($account_id) {
            $query->where('account_id', $account_id);
        }
        if ($category_id) {
            $query->where('category_id', $category_id);
        }
        if ($type) {
            $query->where('type', $type);
        }

        // Total transaksi per member
        $perMember = (clone $query)
            ->select('member_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('member_id')
            ->with('member:id,name')
            ->get();

        // Total transaksi per account
        $perAccount = (clone $query)
            ->select('account_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('account_id')
            ->with('account:id,name')
            ->get();

        // Total transaksi per category
        $perCategory = (clone $query)
            ->select('category_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category_id')
            ->with('category:id,name')
            ->get();

        // Total transaksi per type
        $perType = (clone $query)
            ->select('type', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('type')
            ->get();

        return response()->json([
            'year' => $year,
            'month' => $month,
            'per_member' => $perMember,
            'per_account' => $perAccount,
            'per_category' => $perCategory,
            'per_type' => $perType,
        ]);
    }

    // Laporan Tahunan
    public function yearly(Request $request)
    {
        $user_id = Auth::id();

        $year = $request->query('year', date('Y'));
        $member_id = $request->query('member_id');
        $account_id = $request->query('account_id');
        $category_id = $request->query('category_id');
        $type = $request->query('type'); // income, expense, transfer

        $query = Transaction::where('user_id', $user_id)
            ->whereYear('date', $year);

        if ($member_id) {
            $query->where('member_id', $member_id);
        }
        if ($account_id) {
            $query->where('account_id', $account_id);
        }
        if ($category_id) {
            $query->where('category_id', $category_id);
        }
        if ($type) {
            $query->where('type', $type);
        }

        // Total transaksi per member
        $perMember = (clone $query)
            ->select('member_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('member_id')
            ->with('member:id,name')
            ->get();

        // Total transaksi per account
        $perAccount = (clone $query)
            ->select('account_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('account_id')
            ->with('account:id,name')
            ->get();

        // Total transaksi per category
        $perCategory = (clone $query)
            ->select('category_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category_id')
            ->with('category:id,name')
            ->get();

        // Total transaksi per type
        $perType = (clone $query)
            ->select('type', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('type')
            ->get();

        return response()->json([
            'year' => $year,
            'per_member' => $perMember,
            'per_account' => $perAccount,
            'per_category' => $perCategory,
            'per_type' => $perType,
        ]);
    }
}
