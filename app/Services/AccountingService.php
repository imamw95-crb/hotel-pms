<?php

namespace App\Services;

use App\Exceptions\AccountingException;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\AccountMapping;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountingService
{
    public function accountByCode(string $code): ?Account
    {
        return Account::where('code', $code)->first();
    }

    public function defaultAccount(string $key): ?Account
    {
        $code = config("accounting.default_codes.{$key}");

        return $code ? $this->accountByCode($code) : null;
    }

    public function isPeriodClosed($date): bool
    {
        $period = AccountingPeriod::forDate($date);

        return $period !== null && $period->isClosed();
    }

    public function assertPeriodOpen($date): void
    {
        if ($this->isPeriodClosed($date)) {
            throw new AccountingException(
                'Periode akuntansi '.AccountingPeriod::periodKey($date).' sudah ditutup. Jurnal tidak dapat diubah.'
            );
        }
    }

    /**
     * Akun kas/bank untuk sebuah metode pembayaran.
     */
    public function cashAccountFor(?string $paymentMethod): ?Account
    {
        $slug = $paymentMethod ?: 'cash';

        $mapped = AccountMapping::resolve(AccountMapping::GROUP_PAYMENT_METHOD, $slug);
        if ($mapped) {
            return $mapped;
        }

        $isCash = Str::contains(Str::lower($slug), ['cash', 'tunai']);

        return $this->defaultAccount($isCash ? 'cash' : 'bank') ?? $this->defaultAccount('cash');
    }

    /**
     * Akun pendapatan untuk sebuah tipe transaksi reservasi.
     */
    public function revenueAccountFor(string $transactionType): ?Account
    {
        $mapped = AccountMapping::resolve(AccountMapping::GROUP_REVENUE, $transactionType);
        if ($mapped) {
            return $mapped;
        }

        $key = config("accounting.revenue_by_transaction_type.{$transactionType}", 'room_revenue');

        return $this->defaultAccount($key) ?? $this->defaultAccount('room_revenue');
    }

    /**
     * Akun beban untuk pengeluaran: akun eksplisit → kategori → default.
     */
    public function expenseAccountFor(?string $category, ?int $accountId = null): ?Account
    {
        if ($accountId) {
            $account = Account::find($accountId);
            if ($account) {
                return $account;
            }
        }

        if ($category) {
            $mapped = AccountMapping::resolve(AccountMapping::GROUP_EXPENSE, $category);
            if ($mapped) {
                return $mapped;
            }
        }

        return $this->defaultAccount('default_expense');
    }

    /**
     * Simpan jurnal. Bila source_type + source_id sudah punya jurnal, jurnal lama
     * diganti (idempotent) supaya sinkronisasi ulang tidak menghasilkan duplikat.
     *
     * @param  array<int, array{account_id:int, debit?:float|int|string, credit?:float|int|string, memo?:string|null}>  $lines
     */
    public function post(
        string $sourceType,
        ?int $sourceId,
        $date,
        string $memo,
        array $lines,
        ?int $userId = null
    ): JournalEntry {
        $date = Carbon::parse($date)->toDateString();
        $this->assertPeriodOpen($date);

        $lines = $this->normalize($lines);
        $total = $this->assertBalanced($lines);

        return DB::transaction(function () use ($sourceType, $sourceId, $date, $memo, $lines, $total, $userId) {
            $entry = $sourceId !== null
                ? JournalEntry::where('source_type', $sourceType)->where('source_id', $sourceId)->first()
                : null;

            if ($entry) {
                $this->assertPeriodOpen($entry->date);
                $entry->lines()->delete();
                $entry->fill([
                    'date' => $date,
                    'memo' => $memo,
                    'status' => JournalEntry::STATUS_POSTED,
                    'total_debit' => $total,
                    'created_by' => $userId ?? Auth::id(),
                    'posted_at' => now(),
                    'voided_at' => null,
                    'void_reason' => null,
                ]);
                $entry->save();
            } else {
                $entry = JournalEntry::create([
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                    'date' => $date,
                    'memo' => $memo,
                    'status' => JournalEntry::STATUS_POSTED,
                    'total_debit' => $total,
                    'created_by' => $userId ?? Auth::id(),
                    'posted_at' => now(),
                ]);
            }

            foreach ($lines as $line) {
                $entry->lines()->create($line);
            }

            return $entry->fresh(['lines.account']);
        });
    }

    public function void(JournalEntry $entry, ?string $reason = null): void
    {
        if ($entry->status === JournalEntry::STATUS_VOID) {
            return;
        }

        $this->assertPeriodOpen($entry->date);

        $entry->update([
            'status' => JournalEntry::STATUS_VOID,
            'voided_at' => now(),
            'void_reason' => $reason,
        ]);
    }

    public function voidBySource(string $sourceType, int $sourceId, ?string $reason = null): void
    {
        $entry = JournalEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('status', JournalEntry::STATUS_POSTED)
            ->first();

        if ($entry) {
            $this->void($entry, $reason);
        }
    }

    /**
     * @return array<int, array{account_id:int, debit:float, credit:float, memo:?string}>
     */
    private function normalize(array $lines): array
    {
        $normalized = [];

        foreach ($lines as $line) {
            if (empty($line['account_id'])) {
                continue;
            }

            $debit = round((float) ($line['debit'] ?? 0), 2);
            $credit = round((float) ($line['credit'] ?? 0), 2);

            if ($debit === 0.0 && $credit === 0.0) {
                continue;
            }

            if ($debit !== 0.0 && $credit !== 0.0) {
                throw new AccountingException('Satu baris jurnal hanya boleh berisi debit ATAU kredit.');
            }

            $normalized[] = [
                'account_id' => (int) $line['account_id'],
                'debit' => abs($debit),
                'credit' => abs($credit),
                'memo' => $line['memo'] ?? null,
            ];
        }

        return $normalized;
    }

    private function assertBalanced(array $lines): float
    {
        if (count($lines) < 2) {
            throw new AccountingException('Jurnal minimal terdiri dari dua baris (debit dan kredit).');
        }

        $debit = round(array_sum(array_column($lines, 'debit')), 2);
        $credit = round(array_sum(array_column($lines, 'credit')), 2);

        if (abs($debit - $credit) > 0.005) {
            throw new AccountingException(
                'Jurnal tidak seimbang: total debit '.number_format($debit, 2, ',', '.')
                .' ≠ total kredit '.number_format($credit, 2, ',', '.').'.'
            );
        }

        if ($debit <= 0) {
            throw new AccountingException('Nominal jurnal harus lebih besar dari nol.');
        }

        return $debit;
    }
}
