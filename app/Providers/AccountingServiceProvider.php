<?php

namespace App\Providers;

use App\Exceptions\AccountingException;
use App\Models\Deposit;
use App\Models\Expense;
use App\Models\RestoTransaction;
use App\Models\ServiceCharge;
use App\Models\Transaction;
use App\Services\AccountingPostingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * Sinkronisasi otomatis transaksi operasional hotel ke jurnal akuntansi.
 * Kegagalan posting TIDAK boleh menggagalkan operasi hotel (check-in, payment,
 * expense) — cukup dicatat di log untuk ditindaklanjuti.
 */
class AccountingServiceProvider extends ServiceProvider
{
    private ?bool $ready = null;

    public function boot(): void
    {
        foreach ([Transaction::class, RestoTransaction::class, ServiceCharge::class, Expense::class, Deposit::class] as $model) {
            $model::created(fn (Model $record) => $this->sync($record));
            $model::updated(fn (Model $record) => $this->sync($record));
            $model::deleted(fn (Model $record) => $this->void($record));
        }
    }

    private function sync(Model $record): void
    {
        if (! $this->accountingReady()) {
            return;
        }

        try {
            app(AccountingPostingService::class)->syncModel($record);
        } catch (AccountingException $e) {
            Log::warning('Accounting: jurnal dilewati', [
                'model' => $record::class,
                'id' => $record->getKey(),
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Accounting: gagal membuat jurnal', [
                'model' => $record::class,
                'id' => $record->getKey(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function void(Model $record): void
    {
        if (! $this->accountingReady()) {
            return;
        }

        try {
            app(AccountingPostingService::class)->voidForModel($record);
        } catch (\Throwable $e) {
            Log::warning('Accounting: gagal membatalkan jurnal', [
                'model' => $record::class,
                'id' => $record->getKey(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cek tabel akuntansi sekali saja (dipakai saat event pertama terjadi,
     * bukan saat boot, supaya aman pada instalasi yang belum dimigrasi).
     */
    private function accountingReady(): bool
    {
        if ($this->ready !== null) {
            return $this->ready;
        }

        try {
            return $this->ready = Schema::hasTable('accounts')
                && Schema::hasTable('account_mappings')
                && Schema::hasTable('journal_entries')
                && Schema::hasTable('journal_entry_lines');
        } catch (\Throwable $e) {
            return $this->ready = false;
        }
    }
}
