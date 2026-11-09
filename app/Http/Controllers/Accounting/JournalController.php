<?php

namespace App\Http\Controllers\Accounting;

use App\Exceptions\AccountingException;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $start = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        $source = $request->get('source_type');
        $status = $request->get('status');
        $search = $request->get('search');

        $query = JournalEntry::with('lines.account', 'createdBy')
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->when($source, fn ($q) => $q->where('source_type', $source))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(function ($sub) use ($search) {
                $sub->where('entry_number', 'like', "%{$search}%")->orWhere('memo', 'like', "%{$search}%");
            }))
            ->orderByDesc('date')
            ->orderByDesc('id');

        $entries = $query->paginate(25)->withQueryString();

        $totals = JournalEntry::whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->where('status', JournalEntry::STATUS_POSTED)
            ->when($source, fn ($q) => $q->where('source_type', $source))
            ->selectRaw('COUNT(*) as entries, COALESCE(SUM(total_debit),0) as total')
            ->first();

        return view('accounting.journals.index', compact(
            'entries', 'start', 'end', 'source', 'status', 'search', 'totals'
        ));
    }

    public function create(): View
    {
        return view('accounting.journals.create', [
            'accounts' => $this->accountOptions(),
        ]);
    }

    public function store(Request $request, AccountingService $accounting): RedirectResponse
    {
        $data = $request->validate([
            'date' => 'required|date',
            'memo' => 'nullable|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'nullable|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.memo' => 'nullable|string|max:255',
        ]);

        try {
            $entry = $accounting->post(
                JournalEntry::SOURCE_MANUAL,
                null,
                $data['date'],
                $data['memo'] ?: 'Jurnal manual',
                $data['lines']
            );
        } catch (AccountingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('accounting.journals.show', $entry)
            ->with('success', 'Jurnal '.$entry->entry_number.' berhasil diposting.');
    }

    public function show(JournalEntry $journal): View
    {
        $journal->load('lines.account', 'createdBy');

        return view('accounting.journals.show', ['entry' => $journal]);
    }

    public function void(Request $request, JournalEntry $journal, AccountingService $accounting): RedirectResponse
    {
        $data = $request->validate([
            'void_reason' => 'nullable|string|max:255',
        ]);

        try {
            $accounting->void($journal, $data['void_reason'] ?? 'Dibatalkan oleh '.auth()->user()?->name);
        } catch (AccountingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Jurnal '.$journal->entry_number.' dibatalkan.');
    }

    private function accountOptions()
    {
        return Account::active()->orderBy('code')->get()->groupBy('type');
    }
}
