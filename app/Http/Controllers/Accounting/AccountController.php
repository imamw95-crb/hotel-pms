<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->get('type');
        $search = $request->get('search');
        $status = $request->get('status', 'active');

        $accounts = Account::query()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($search, fn ($q) => $q->where(function ($sub) use ($search) {
                $sub->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%");
            }))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('code')
            ->get();

        $balances = $this->balances($accounts->pluck('id')->all());

        return view('accounting.accounts.index', [
            'accounts' => $accounts->groupBy('type'),
            'balances' => $balances,
            'type' => $type,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('accounting.accounts.form', [
            'account' => new Account(['type' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data = $this->normalizeCashFlag($data);

        Account::create($data);

        return redirect()->route('accounting.accounts.index')->with('success', 'Akun berhasil dibuat.');
    }

    public function edit(Account $account): View
    {
        return view('accounting.accounts.form', compact('account'));
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $data = $this->validated($request, $account);
        $data = $this->normalizeCashFlag($data);

        $account->update($data);

        return redirect()->route('accounting.accounts.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        if ($account->lines()->exists()) {
            return back()->with('error', 'Akun tidak dapat dihapus karena sudah memiliki jurnal. Nonaktifkan saja akun ini.');
        }

        $account->delete();

        return redirect()->route('accounting.accounts.index')->with('success', 'Akun berhasil dihapus.');
    }

    private function validated(Request $request, ?Account $account = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('accounts', 'code')->ignore($account?->id)],
            'name' => 'required|string|max:120',
            'type' => 'required|in:'.implode(',', array_keys(Account::TYPES)),
            'group_label' => 'nullable|string|max:80',
            'normal_balance' => 'required|in:debit,credit',
            'is_cash' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }

    private function normalizeCashFlag(array $data): array
    {
        $data['is_cash'] = ($data['type'] === 'asset') && (bool) ($data['is_cash'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    /**
     * Saldo berjalan (sampai hari ini) untuk daftar akun.
     */
    private function balances(array $accountIds): array
    {
        if (empty($accountIds)) {
            return [];
        }

        return DB::table('journal_entry_lines as l')
            ->join('journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->where('e.status', JournalEntry::STATUS_POSTED)
            ->whereDate('e.date', '<=', Carbon::today()->toDateString())
            ->whereIn('l.account_id', $accountIds)
            ->groupBy('l.account_id')
            ->selectRaw('l.account_id as account_id, SUM(l.debit) - SUM(l.credit) as net')
            ->pluck('net', 'account_id')
            ->map(fn ($net) => round((float) $net, 2))
            ->all();
    }
}
