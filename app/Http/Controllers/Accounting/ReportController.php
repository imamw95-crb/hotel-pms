<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountingReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function trialBalance(Request $request, AccountingReportService $reports): View
    {
        [$start, $end] = $this->period($request);

        return view('accounting.reports.trial-balance', [
            'report' => $reports->trialBalance($start, $end),
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function generalLedger(Request $request, AccountingReportService $reports): View
    {
        [$start, $end] = $this->period($request);
        $accountId = $request->integer('account_id') ?: null;

        return view('accounting.reports.ledger', [
            'report' => $accountId ? $reports->generalLedger($accountId, $start, $end) : null,
            'accounts' => Account::orderBy('code')->get(),
            'accountId' => $accountId,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function incomeStatement(Request $request, AccountingReportService $reports): View
    {
        [$start, $end] = $this->period($request);

        return view('accounting.reports.income-statement', [
            'report' => $reports->incomeStatement($start, $end),
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function balanceSheet(Request $request, AccountingReportService $reports): View
    {
        $asOf = $request->get('as_of', Carbon::today()->toDateString());

        return view('accounting.reports.balance-sheet', [
            'report' => $reports->balanceSheet($asOf),
            'asOf' => $asOf,
        ]);
    }

    public function cashFlow(Request $request, AccountingReportService $reports): View
    {
        [$start, $end] = $this->period($request);

        return view('accounting.reports.cash-flow', [
            'report' => $reports->cashFlow($start, $end),
            'start' => $start,
            'end' => $end,
        ]);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function period(Request $request): array
    {
        $start = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        return [$start, $end];
    }
}
