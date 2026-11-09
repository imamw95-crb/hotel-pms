<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccountingReportService
{
    /**
     * Mutasi (debit/kredit) per akun pada rentang tanggal tertentu.
     */
    private function movements(string $from, string $to): Collection
    {
        return DB::table('journal_entry_lines as l')
            ->join('journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->where('e.status', JournalEntry::STATUS_POSTED)
            ->whereDate('e.date', '>=', $from)
            ->whereDate('e.date', '<=', $to)
            ->groupBy('l.account_id')
            ->selectRaw('l.account_id as account_id, SUM(l.debit) as debit, SUM(l.credit) as credit')
            ->get()
            ->keyBy('account_id');
    }

    /**
     * Mutasi per akun sampai tanggal tertentu (dipakai untuk saldo awal).
     */
    private function movementsUpTo(string $asOf): Collection
    {
        return DB::table('journal_entry_lines as l')
            ->join('journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->where('e.status', JournalEntry::STATUS_POSTED)
            ->whereDate('e.date', '<=', $asOf)
            ->groupBy('l.account_id')
            ->selectRaw('l.account_id as account_id, SUM(l.debit) as debit, SUM(l.credit) as credit')
            ->get()
            ->keyBy('account_id');
    }

    private function net($row): float
    {
        return round((float) ($row->debit ?? 0) - (float) ($row->credit ?? 0), 2);
    }

    /**
     * Neraca Saldo: saldo awal + mutasi periode = saldo akhir.
     */
    public function trialBalance(string $start, string $end): array
    {
        $opening = $this->movementsUpTo(Carbon::parse($start)->subDay()->toDateString());
        $period = $this->movements($start, $end);

        $rows = [];
        $totals = [
            'opening_debit' => 0.0, 'opening_credit' => 0.0,
            'debit' => 0.0, 'credit' => 0.0,
            'ending_debit' => 0.0, 'ending_credit' => 0.0,
        ];

        foreach (Account::orderBy('code')->get() as $account) {
            $openingNet = $this->net($opening->get($account->id));
            $periodRow = $period->get($account->id);
            $debit = round((float) ($periodRow->debit ?? 0), 2);
            $credit = round((float) ($periodRow->credit ?? 0), 2);
            $endingNet = round($openingNet + $debit - $credit, 2);

            if ($openingNet === 0.0 && $debit === 0.0 && $credit === 0.0 && $endingNet === 0.0) {
                continue;
            }

            $rows[] = [
                'account' => $account,
                'opening_debit' => $openingNet > 0 ? $openingNet : 0.0,
                'opening_credit' => $openingNet < 0 ? -$openingNet : 0.0,
                'debit' => $debit,
                'credit' => $credit,
                'ending_debit' => $endingNet > 0 ? $endingNet : 0.0,
                'ending_credit' => $endingNet < 0 ? -$endingNet : 0.0,
            ];

            $totals['opening_debit'] += $openingNet > 0 ? $openingNet : 0.0;
            $totals['opening_credit'] += $openingNet < 0 ? -$openingNet : 0.0;
            $totals['debit'] += $debit;
            $totals['credit'] += $credit;
            $totals['ending_debit'] += $endingNet > 0 ? $endingNet : 0.0;
            $totals['ending_credit'] += $endingNet < 0 ? -$endingNet : 0.0;
        }

        return ['rows' => $rows, 'totals' => array_map(fn ($v) => round($v, 2), $totals)];
    }

    /**
     * Buku Besar satu akun beserta saldo berjalan.
     */
    public function generalLedger(int $accountId, string $start, string $end): array
    {
        $account = Account::findOrFail($accountId);

        $opening = $this->net($this->movementsUpTo(Carbon::parse($start)->subDay()->toDateString())->get($accountId));

        $lines = DB::table('journal_entry_lines as l')
            ->join('journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->where('e.status', JournalEntry::STATUS_POSTED)
            ->where('l.account_id', $accountId)
            ->whereDate('e.date', '>=', $start)
            ->whereDate('e.date', '<=', $end)
            ->orderBy('e.date')
            ->orderBy('e.id')
            ->orderBy('l.id')
            ->select([
                'e.id as entry_id',
                'e.entry_number',
                'e.date',
                'e.memo as entry_memo',
                'e.source_type',
                'l.debit',
                'l.credit',
                'l.memo',
            ])
            ->get();

        $running = $opening;
        $rows = [];

        foreach ($lines as $line) {
            $running = round($running + (float) $line->debit - (float) $line->credit, 2);
            $rows[] = [
                'date' => $line->date,
                'entry_id' => $line->entry_id,
                'entry_number' => $line->entry_number,
                'memo' => $line->memo ?: $line->entry_memo,
                'source_type' => $line->source_type,
                'debit' => (float) $line->debit,
                'credit' => (float) $line->credit,
                'balance' => $running,
            ];
        }

        return [
            'account' => $account,
            'opening' => $opening,
            'rows' => $rows,
            'total_debit' => round(array_sum(array_column($rows, 'debit')), 2),
            'total_credit' => round(array_sum(array_column($rows, 'credit')), 2),
            'ending' => $running,
        ];
    }

    /**
     * Laba Rugi per department (mengikuti struktur USALI sederhana) + margin.
     */
    public function incomeStatement(string $start, string $end): array
    {
        $period = $this->movements($start, $end);

        $revenue = $this->groupByType($period, 'revenue', fn ($row) => (float) $row->credit - (float) $row->debit);
        $expense = $this->groupByType($period, 'expense', fn ($row) => (float) $row->debit - (float) $row->credit);

        $totalRevenue = round($revenue->sum('total'), 2);
        $totalExpense = round($expense->sum('total'), 2);
        $netIncome = round($totalRevenue - $totalExpense, 2);

        return [
            'revenue' => $revenue,
            'total_revenue' => $totalRevenue,
            'expense' => $expense,
            'total_expense' => $totalExpense,
            'net_income' => $netIncome,
            'margin' => $totalRevenue > 0 ? round($netIncome / $totalRevenue * 100, 2) : 0.0,
        ];
    }

    /**
     * Neraca per tanggal tertentu. Laba/rugi periode berjalan otomatis masuk ekuitas.
     */
    public function balanceSheet(string $asOf): array
    {
        $upTo = $this->movementsUpTo($asOf);

        $assets = $this->groupByType($upTo, 'asset', fn ($row) => (float) $row->debit - (float) $row->credit);
        $liabilities = $this->groupByType($upTo, 'liability', fn ($row) => (float) $row->credit - (float) $row->debit);
        $equity = $this->groupByType($upTo, 'equity', fn ($row) => (float) $row->credit - (float) $row->debit);

        $revenueTotal = round($this->groupByType($upTo, 'revenue', fn ($row) => (float) $row->credit - (float) $row->debit)->sum('total'), 2);
        $expenseTotal = round($this->groupByType($upTo, 'expense', fn ($row) => (float) $row->debit - (float) $row->credit)->sum('total'), 2);
        $currentEarnings = round($revenueTotal - $expenseTotal, 2);

        $totalAssets = round($assets->sum('total'), 2);
        $totalLiabilities = round($liabilities->sum('total'), 2);
        $totalEquity = round($equity->sum('total') + $currentEarnings, 2);

        return [
            'assets' => $assets,
            'total_assets' => $totalAssets,
            'liabilities' => $liabilities,
            'total_liabilities' => $totalLiabilities,
            'equity' => $equity,
            'current_earnings' => $currentEarnings,
            'total_equity' => $totalEquity,
            'liabilities_and_equity' => round($totalLiabilities + $totalEquity, 2),
            'balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }

    /**
     * Arus kas (metode langsung): saldo awal, penerimaan, pengeluaran, saldo akhir
     * per akun kas/bank, plus rekap per jenis aktivitas.
     */
    public function cashFlow(string $start, string $end): array
    {
        $cashAccounts = Account::cash()->orderBy('code')->get();
        $period = $this->movements($start, $end);

        $rows = [];
        $totals = ['opening' => 0.0, 'receipts' => 0.0, 'payments' => 0.0, 'closing' => 0.0];

        foreach ($cashAccounts as $account) {
            $opening = $this->net($this->movementsUpTo(Carbon::parse($start)->subDay()->toDateString())->get($account->id));
            $receipts = round((float) ($period->get($account->id)->debit ?? 0), 2);
            $payments = round((float) ($period->get($account->id)->credit ?? 0), 2);
            $closing = round($opening + $receipts - $payments, 2);

            $rows[] = compact('account', 'opening', 'receipts', 'payments', 'closing');

            $totals['opening'] += $opening;
            $totals['receipts'] += $receipts;
            $totals['payments'] += $payments;
            $totals['closing'] += $closing;
        }

        return [
            'rows' => $rows,
            'totals' => array_map(fn ($v) => round($v, 2), $totals),
            'receipts_by_source' => $this->cashBySource($start, $end, 'debit'),
            'payments_by_source' => $this->cashBySource($start, $end, 'credit'),
        ];
    }

    /**
     * Rekap arus kas per jenis aktivitas (sumber transaksi).
     */
    private function cashBySource(string $start, string $end, string $side): Collection
    {
        $column = $side === 'debit' ? 'l.debit' : 'l.credit';

        return DB::table('journal_entry_lines as l')
            ->join('journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'l.account_id')
            ->where('e.status', JournalEntry::STATUS_POSTED)
            ->where('a.is_cash', true)
            ->whereDate('e.date', '>=', $start)
            ->whereDate('e.date', '<=', $end)
            ->where($column, '>', 0)
            ->groupBy('e.source_type')
            ->selectRaw("e.source_type as source_type, SUM({$column}) as amount")
            ->orderByDesc('amount')
            ->get()
            ->map(fn ($row) => (object) [
                'label' => JournalEntry::SOURCE_LABELS[$row->source_type] ?? ucfirst((string) $row->source_type),
                'amount' => round((float) $row->amount, 2),
            ]);
    }

    /**
     * Kelompokkan mutasi akun per tipe, dengan sub-total per group_label.
     */
    private function groupByType(Collection $movements, string $type, callable $amountResolver): Collection
    {
        $accounts = Account::ofType($type)->orderBy('code')->get();
        $groups = [];

        foreach ($accounts as $account) {
            $row = $movements->get($account->id);

            if (! $row) {
                continue;
            }

            $amount = round($amountResolver($row), 2);

            if ($amount === 0.0) {
                continue;
            }

            $groupKey = $account->group_label ?: $account->type_label;
            $groups[$groupKey] ??= ['group' => $groupKey, 'items' => collect(), 'total' => 0.0];
            $groups[$groupKey]['items']->push((object) ['account' => $account, 'amount' => $amount]);
            $groups[$groupKey]['total'] = round($groups[$groupKey]['total'] + $amount, 2);
        }

        return collect(array_values($groups))->map(fn ($group) => (object) [
            'group' => $group['group'],
            'items' => $group['items'],
            'total' => $group['total'],
        ]);
    }
}
