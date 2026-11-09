<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeriodController extends Controller
{
    public function index(): View
    {
        $periods = collect(range(0, 11))->map(function ($offset) {
            $month = Carbon::now()->startOfMonth()->subMonths($offset);
            $key = $month->format('Y-m');
            $period = AccountingPeriod::where('period', $key)->first();

            $stats = JournalEntry::where('status', JournalEntry::STATUS_POSTED)
                ->whereDate('date', '>=', $month->copy()->startOfMonth()->toDateString())
                ->whereDate('date', '<=', $month->copy()->endOfMonth()->toDateString())
                ->selectRaw('COUNT(*) as entries, COALESCE(SUM(total_debit),0) as total')
                ->first();

            return (object) [
                'period' => $key,
                'label' => $month->copy()->locale('id')->translatedFormat('F Y'),
                'status' => $period?->status ?? AccountingPeriod::STATUS_OPEN,
                'closed_at' => $period?->closed_at,
                'entries' => (int) ($stats->entries ?? 0),
                'total' => (float) ($stats->total ?? 0),
            ];
        });

        return view('accounting.periods.index', compact('periods'));
    }

    public function close(string $period): RedirectResponse
    {
        AccountingPeriod::updateOrCreate(
            ['period' => $period],
            ['status' => AccountingPeriod::STATUS_CLOSED, 'closed_at' => now(), 'closed_by' => auth()->id()]
        );

        return back()->with('success', 'Periode '.$period.' ditutup. Jurnal pada periode ini tidak bisa diubah lagi.');
    }

    public function reopen(string $period): RedirectResponse
    {
        AccountingPeriod::where('period', $period)->update([
            'status' => AccountingPeriod::STATUS_OPEN,
            'closed_at' => null,
            'closed_by' => null,
        ]);

        return back()->with('success', 'Periode '.$period.' dibuka kembali.');
    }
}
