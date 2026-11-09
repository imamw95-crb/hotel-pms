<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Services\AccountingReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, AccountingReportService $reports): View
    {
        $start = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $income = $reports->incomeStatement($start, $end);
        $balance = $reports->balanceSheet($end);
        $cash = $reports->cashFlow($start, $end);

        $recentEntries = JournalEntry::with('lines')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $lastPeriod = AccountingPeriod::orderByDesc('period')->first();
        $activeAccounts = Account::active()->count();

        return view('accounting.dashboard', compact(
            'start',
            'end',
            'income',
            'balance',
            'cash',
            'recentEntries',
            'lastPeriod',
            'activeAccounts'
        ));
    }
}
