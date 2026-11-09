<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountMapping;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $this->syncPaymentMethodMappings();

        $mappings = AccountMapping::with('account')
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('accounting.settings.index', [
            'mappings' => $mappings,
            'cashAccounts' => Account::active()->where('type', 'asset')->orderBy('code')->get(),
            'revenueAccounts' => Account::active()->ofType('revenue')->orderBy('code')->get(),
            'expenseAccounts' => Account::active()->ofType('expense')->orderBy('code')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mappings' => 'array',
            'mappings.*' => 'nullable|exists:accounts,id',
        ]);

        foreach ($data['mappings'] ?? [] as $id => $accountId) {
            AccountMapping::whereKey($id)->update(['account_id' => $accountId ?: null]);
        }

        return redirect()->route('accounting.settings.index')->with('success', 'Pemetaan akun berhasil disimpan.');
    }

    /**
     * Pastikan setiap metode pembayaran punya baris pemetaan akun.
     */
    private function syncPaymentMethodMappings(): void
    {
        PaymentMethod::orderBy('name')->get()->each(function (PaymentMethod $method) {
            AccountMapping::firstOrCreate(
                ['group' => AccountMapping::GROUP_PAYMENT_METHOD, 'key' => $method->slug],
                ['label' => $method->name, 'is_active' => true]
            );
        });
    }
}
