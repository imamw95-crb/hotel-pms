<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountMapping;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Chart of Accounts mengikuti struktur USALI yang disederhanakan untuk hotel
 * skala kecil–menengah (departemen Rooms, F&B, dan undistributed expenses).
 */
class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAccounts();
        $this->seedPaymentMethodMappings();
        $this->seedRevenueMappings();
        $this->seedExpenseMappings();
    }

    private function seedAccounts(): void
    {
        $accounts = [
            // ── ASET ─────────────────────────────────────────────
            ['1-1100', 'Kas (Tunai)', 'asset', 'Aset Lancar', 'debit', true],
            ['1-1200', 'Bank BCA', 'asset', 'Aset Lancar', 'debit', true],
            ['1-1210', 'Bank Mandiri', 'asset', 'Aset Lancar', 'debit', true],
            ['1-1220', 'Bank Lain', 'asset', 'Aset Lancar', 'debit', true],
            ['1-1300', 'Clearing Kartu (EDC)', 'asset', 'Aset Lancar', 'debit', true],
            ['1-1400', 'Clearing QRIS / E-Wallet', 'asset', 'Aset Lancar', 'debit', true],
            ['1-1500', 'Piutang Usaha (City Ledger)', 'asset', 'Aset Lancar', 'debit', false],
            ['1-1600', 'Persediaan', 'asset', 'Aset Lancar', 'debit', false],
            ['1-1700', 'Perlengkapan (Supplies)', 'asset', 'Aset Lancar', 'debit', false],
            ['1-1800', 'Sewa Dibayar di Muka', 'asset', 'Aset Lancar', 'debit', false],
            ['1-2000', 'Peralatan & Inventaris', 'asset', 'Aset Tetap', 'debit', false],
            ['1-2010', 'Akumulasi Penyusutan', 'asset', 'Aset Tetap', 'credit', false],

            // ── LIABILITAS ───────────────────────────────────────
            ['2-1100', 'Uang Jaminan Tamu (Guest Deposit)', 'liability', 'Liabilitas Jangka Pendek', 'credit', false],
            ['2-1200', 'Utang Usaha', 'liability', 'Liabilitas Jangka Pendek', 'credit', false],
            ['2-1300', 'Utang Pajak (PB1/PB2 & PPh)', 'liability', 'Liabilitas Jangka Pendek', 'credit', false],
            ['2-1400', 'Utang Gaji & THR', 'liability', 'Liabilitas Jangka Pendek', 'credit', false],
            ['2-1500', 'Pendapatan Diterima di Muka', 'liability', 'Liabilitas Jangka Pendek', 'credit', false],
            ['2-1600', 'Utang OTA (Komisi)', 'liability', 'Liabilitas Jangka Pendek', 'credit', false],

            // ── EKUITAS ──────────────────────────────────────────
            ['3-1000', 'Modal Pemilik', 'equity', 'Ekuitas', 'credit', false],
            ['3-2000', 'Laba Ditahan', 'equity', 'Ekuitas', 'credit', false],
            ['3-3000', 'Prive / Pengambilan Pemilik', 'equity', 'Ekuitas', 'debit', false],

            // ── PENDAPATAN ───────────────────────────────────────
            ['4-1000', 'Pendapatan Kamar', 'revenue', 'Pendapatan Kamar', 'credit', false],
            ['4-1010', 'Diskon Kamar', 'revenue', 'Pendapatan Kamar', 'debit', false],
            ['4-1100', 'Pendapatan Restoran', 'revenue', 'Pendapatan F&B', 'credit', false],
            ['4-1110', 'Pendapatan Minuman', 'revenue', 'Pendapatan F&B', 'credit', false],
            ['4-1200', 'Pendapatan Laundry', 'revenue', 'Pendapatan Lain-lain', 'credit', false],
            ['4-1210', 'Pendapatan Sewa Ruang / Event', 'revenue', 'Pendapatan Lain-lain', 'credit', false],
            ['4-1220', 'Pendapatan Lain-lain', 'revenue', 'Pendapatan Lain-lain', 'credit', false],
            ['4-1300', 'Service Charge', 'revenue', 'Pendapatan Lain-lain', 'credit', false],
            ['4-9000', 'Diskon, Refund & Koreksi', 'revenue', 'Pengurang Pendapatan', 'debit', false],

            // ── BEBAN KAMAR ──────────────────────────────────────
            ['5-1100', 'Beban Gaji Kamar & Front Office', 'expense', 'Beban Kamar', 'debit', false],
            ['5-1200', 'Beban Amenities & Supplies Kamar', 'expense', 'Beban Kamar', 'debit', false],
            ['5-1300', 'Beban Laundry & Linen', 'expense', 'Beban Kamar', 'debit', false],

            // ── BEBAN F&B ────────────────────────────────────────
            ['5-2100', 'Beban Bahan Makanan & Minuman', 'expense', 'Beban F&B', 'debit', false],
            ['5-2200', 'Beban Gaji Restoran', 'expense', 'Beban F&B', 'debit', false],

            // ── BEBAN UNDISTRIBUTED ──────────────────────────────
            ['6-1100', 'Beban Gaji Administrasi', 'expense', 'Beban Administratif & Umum', 'debit', false],
            ['6-1200', 'Beban Listrik & Air', 'expense', 'Beban Utilitas', 'debit', false],
            ['6-1300', 'Beban Internet & Telepon', 'expense', 'Beban Utilitas', 'debit', false],
            ['6-1400', 'Beban ATK & Administrasi', 'expense', 'Beban Administratif & Umum', 'debit', false],
            ['6-1500', 'Beban Perawatan & Perbaikan', 'expense', 'Beban Administratif & Umum', 'debit', false],
            ['6-1600', 'Beban Sewa Bangunan', 'expense', 'Beban Administratif & Umum', 'debit', false],
            ['6-1700', 'Beban Penyusutan', 'expense', 'Beban Administratif & Umum', 'debit', false],
            ['6-1800', 'Beban Pemasaran & Komisi OTA', 'expense', 'Beban Penjualan & Pemasaran', 'debit', false],
            ['6-1900', 'Beban Pajak & Perizinan', 'expense', 'Beban Administratif & Umum', 'debit', false],
            ['6-9000', 'Beban Lain-lain', 'expense', 'Beban Lain-lain', 'debit', false],
        ];

        foreach ($accounts as $index => $row) {
            Account::updateOrCreate(
                ['code' => $row[0]],
                [
                    'name' => $row[1],
                    'type' => $row[2],
                    'group_label' => $row[3],
                    'normal_balance' => $row[4],
                    'is_cash' => $row[5],
                    'is_active' => true,
                    'sort_order' => ($index + 1) * 10,
                ]
            );
        }
    }

    private function seedPaymentMethodMappings(): void
    {
        PaymentMethod::orderBy('name')->get()->each(function (PaymentMethod $method) {
            AccountMapping::updateOrCreate(
                ['group' => AccountMapping::GROUP_PAYMENT_METHOD, 'key' => $method->slug],
                ['label' => $method->name, 'account_id' => $this->accountFor($method->slug)?->id]
            );
        });
    }

    private function seedRevenueMappings(): void
    {
        $types = [
            'dp', 'pelunasan', 'checkin_payment', 'checkout_payment', 'additional',
            'extend', 'adjustment', 'edc_payment', 'ota_payment', 'payment',
            'refund', 'koreksi',
        ];

        foreach ($types as $type) {
            $isContra = in_array($type, ['refund', 'koreksi'], true);

            AccountMapping::updateOrCreate(
                ['group' => AccountMapping::GROUP_REVENUE, 'key' => $type],
                [
                    'label' => Str::headline($type),
                    'account_id' => Account::byCode($isContra ? '4-9000' : '4-1000')?->id,
                ]
            );
        }
    }

    private function seedExpenseMappings(): void
    {
        foreach (config('accounting.expense_categories', []) as $key => $label) {
            $code = config("accounting.expense_category_accounts.{$key}");

            AccountMapping::updateOrCreate(
                ['group' => AccountMapping::GROUP_EXPENSE, 'key' => $key],
                ['label' => $label, 'account_id' => $code ? Account::byCode($code)?->id : null]
            );
        }
    }

    private function accountFor(string $slug): ?Account
    {
        $slug = Str::lower($slug);

        $code = match (true) {
            Str::contains($slug, ['qris', 'wallet', 'ewallet']) => '1-1400',
            Str::contains($slug, ['edc', 'card', 'kartu', 'credit', 'debit']) => '1-1300',
            Str::contains($slug, ['cash', 'tunai']) => '1-1100',
            Str::contains($slug, ['mandiri']) => '1-1210',
            default => '1-1200',
        };

        return Account::byCode($code);
    }
}
