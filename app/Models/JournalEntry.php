<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    public const STATUS_POSTED = 'posted';

    public const STATUS_VOID = 'void';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_OPENING = 'opening';

    public const SOURCE_LABELS = [
        'manual' => 'Jurnal Manual',
        'opening' => 'Saldo Awal',
        'transaction' => 'Pembayaran Reservasi',
        'resto' => 'Pendapatan Resto',
        'service_charge' => 'Other Revenue',
        'expense' => 'Pengeluaran',
        'deposit' => 'Deposit Tamu',
        'deposit_return' => 'Pengembalian Deposit',
    ];

    protected $fillable = [
        'entry_number', 'date', 'memo', 'source_type', 'source_id',
        'status', 'total_debit', 'created_by', 'posted_at', 'voided_at', 'void_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'total_debit' => 'decimal:2',
        'posted_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $entry) {
            if (empty($entry->entry_number)) {
                $entry->entry_number = self::nextNumber($entry->date ?? now());
            }

            $entry->posted_at ??= now();
        });
    }

    public static function nextNumber($date): string
    {
        $date = $date instanceof \DateTimeInterface
            ? Carbon::instance($date)
            : Carbon::parse($date);

        $prefix = 'JU-'.$date->format('Ym').'-';
        $last = static::where('entry_number', 'like', $prefix.'%')
            ->orderByDesc('entry_number')
            ->value('entry_number');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePosted($query)
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    public function scopeBetween($query, string $start, string $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCE_LABELS[$this->source_type] ?? ucfirst((string) $this->source_type);
    }

    public function getIsVoidAttribute(): bool
    {
        return $this->status === self::STATUS_VOID;
    }
}
