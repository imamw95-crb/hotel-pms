<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\RestoTransaction;
use App\Models\ServiceCharge;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Menjembatani transaksi operasional hotel ke jurnal akuntansi (double-entry,
 * basis kas). Semua nominal dicatat saat uang benar-benar diterima/dikeluarkan.
 */
class AccountingPostingService
{
    public function __construct(private AccountingService $ledger)
    {
    }

    public function syncTransaction(Transaction $transaction): ?JournalEntry
    {
        $amount = round(abs((float) $transaction->amount), 2);

        if ($amount <= 0) {
            $this->ledger->voidBySource('transaction', $transaction->id, 'Nominal transaksi nol');

            return null;
        }

        $cash = $this->ledger->cashAccountFor($transaction->payment_method);
        $revenue = $this->ledger->revenueAccountFor((string) $transaction->type);

        if (! $cash || ! $revenue) {
            return $this->unmapped('transaction', $transaction->id, 'akun kas/pendapatan tidak ditemukan');
        }

        $isOutflow = (float) $transaction->amount < 0
            || in_array((string) $transaction->type, config('accounting.outflow_transaction_types', []), true);

        $reservationNumber = $transaction->reservation?->reservation_number;
        $memo = trim(sprintf(
            '%s %s%s',
            $isOutflow ? 'Refund' : 'Pembayaran',
            $transaction->transaction_number,
            $reservationNumber ? ' — '.$reservationNumber : ''
        ));

        $lines = $isOutflow
            ? [
                ['account_id' => $revenue->id, 'debit' => $amount, 'memo' => $memo],
                ['account_id' => $cash->id, 'credit' => $amount, 'memo' => $memo],
            ]
            : [
                ['account_id' => $cash->id, 'debit' => $amount, 'memo' => $memo],
                ['account_id' => $revenue->id, 'credit' => $amount, 'memo' => $memo],
            ];

        return $this->ledger->post('transaction', $transaction->id, $transaction->created_at ?? now(), $memo, $lines);
    }

    public function syncResto(RestoTransaction $resto): ?JournalEntry
    {
        $amount = round((float) $resto->total_amount, 2);

        if ($amount <= 0) {
            $this->ledger->voidBySource('resto', $resto->id, 'Nominal transaksi nol');

            return null;
        }

        $cash = $this->ledger->cashAccountFor($resto->payment_method);
        $revenue = $this->ledger->defaultAccount('resto_revenue');

        if (! $cash || ! $revenue) {
            return $this->unmapped('resto', $resto->id, 'akun kas/pendapatan resto tidak ditemukan');
        }

        $memo = 'Pendapatan resto '.$resto->transaction_number;

        return $this->ledger->post('resto', $resto->id, $resto->created_at ?? now(), $memo, [
            ['account_id' => $cash->id, 'debit' => $amount, 'memo' => $memo],
            ['account_id' => $revenue->id, 'credit' => $amount, 'memo' => $memo],
        ]);
    }

    public function syncServiceCharge(ServiceCharge $charge): ?JournalEntry
    {
        $amount = round((float) ($charge->total_amount ?: $charge->amount), 2);

        if ($amount <= 0) {
            $this->ledger->voidBySource('service_charge', $charge->id, 'Nominal transaksi nol');

            return null;
        }

        $cash = $this->ledger->cashAccountFor($charge->payment_method);
        $revenue = $this->ledger->defaultAccount('other_revenue');

        if (! $cash || ! $revenue) {
            return $this->unmapped('service_charge', $charge->id, 'akun kas/pendapatan lain tidak ditemukan');
        }

        $memo = 'Other revenue '.$charge->charge_number.($charge->service_name ? ' — '.$charge->service_name : '');

        return $this->ledger->post(
            'service_charge',
            $charge->id,
            $charge->charge_date ?? $charge->created_at ?? now(),
            $memo,
            [
                ['account_id' => $cash->id, 'debit' => $amount, 'memo' => $memo],
                ['account_id' => $revenue->id, 'credit' => $amount, 'memo' => $memo],
            ]
        );
    }

    public function syncExpense(Expense $expense): ?JournalEntry
    {
        $amount = round((float) $expense->amount, 2);

        if ($amount <= 0) {
            $this->ledger->voidBySource('expense', $expense->id, 'Nominal pengeluaran nol');

            return null;
        }

        $cash = $this->ledger->cashAccountFor($expense->payment_method);
        $expenseAccount = $this->ledger->expenseAccountFor($expense->expense_category, $expense->account_id);

        if (! $cash || ! $expenseAccount) {
            return $this->unmapped('expense', $expense->id, 'akun kas/beban tidak ditemukan');
        }

        $memo = 'Pengeluaran '.$expense->expense_number.' — '.$expense->description;

        return $this->ledger->post(
            'expense',
            $expense->id,
            $expense->expense_date ?? $expense->created_at ?? now(),
            mb_substr($memo, 0, 255),
            [
                ['account_id' => $expenseAccount->id, 'debit' => $amount, 'memo' => $expense->description],
                ['account_id' => $cash->id, 'credit' => $amount, 'memo' => $expense->description],
            ]
        );
    }

    public function syncDeposit(Deposit $deposit): ?JournalEntry
    {
        $amount = round((float) $deposit->total_amount, 2);

        if ($amount <= 0) {
            $this->ledger->voidBySource('deposit', $deposit->id, 'Nominal deposit nol');

            return null;
        }

        $cash = $this->ledger->cashAccountFor($deposit->payment_method);
        $liability = $this->ledger->defaultAccount('guest_deposit');

        if (! $cash || ! $liability) {
            return $this->unmapped('deposit', $deposit->id, 'akun kas/liabilitas deposit tidak ditemukan');
        }

        $returned = $deposit->status === 'returned';
        $receiptMemo = 'Penerimaan deposit kartu '.$deposit->receipt_number;

        // Penerimaan deposit = uang masuk (liabilitas), harus tetap ada walau
        // deposit sudah dikembalikan — pengembalian dicatat sebagai jurnal baru.
        $receipt = $this->ledger->post('deposit', $deposit->id, $deposit->created_at ?? now(), $receiptMemo, [
            ['account_id' => $cash->id, 'debit' => $amount, 'memo' => $receiptMemo],
            ['account_id' => $liability->id, 'credit' => $amount, 'memo' => $receiptMemo],
        ]);

        if ($returned) {
            $returnMemo = 'Pengembalian deposit kartu '.$deposit->receipt_number;

            $this->ledger->post(
                'deposit_return',
                $deposit->id,
                $deposit->updated_at ?? now(),
                $returnMemo,
                [
                    ['account_id' => $liability->id, 'debit' => $amount, 'memo' => $returnMemo],
                    ['account_id' => $cash->id, 'credit' => $amount, 'memo' => $returnMemo],
                ]
            );
        } else {
            $this->ledger->voidBySource('deposit_return', $deposit->id, 'Deposit kembali aktif');
        }

        return $receipt;
    }

    /**
     * Batalkan jurnal milik sebuah record operasional (dipakai saat record dihapus).
     */
    public function voidForModel(Model $model, ?string $reason = null): void
    {
        $sourceType = $this->sourceTypeFor($model);

        if ($sourceType === null || ! $model->getKey()) {
            return;
        }

        $this->ledger->voidBySource($sourceType, (int) $model->getKey(), $reason ?? 'Data sumber dihapus');

        if ($model instanceof Deposit) {
            $this->ledger->voidBySource('deposit_return', (int) $model->getKey(), $reason ?? 'Data sumber dihapus');
        }
    }

    public function syncModel(Model $model): ?JournalEntry
    {
        return match (true) {
            $model instanceof Transaction => $this->syncTransaction($model),
            $model instanceof RestoTransaction => $this->syncResto($model),
            $model instanceof ServiceCharge => $this->syncServiceCharge($model),
            $model instanceof Expense => $this->syncExpense($model),
            $model instanceof Deposit => $this->syncDeposit($model),
            default => null,
        };
    }

    public function sourceTypeFor(Model $model): ?string
    {
        return match (true) {
            $model instanceof Transaction => 'transaction',
            $model instanceof RestoTransaction => 'resto',
            $model instanceof ServiceCharge => 'service_charge',
            $model instanceof Expense => 'expense',
            $model instanceof Deposit => 'deposit',
            default => null,
        };
    }

    private function unmapped(string $sourceType, int $sourceId, string $reason): ?JournalEntry
    {
        Log::warning('Accounting: jurnal tidak dibuat', [
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'reason' => $reason,
        ]);

        return null;
    }
}
