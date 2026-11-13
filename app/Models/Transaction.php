<?php

namespace App\Models;

use App\Services\NightAuditLockService;
use App\Traits\Blameable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;
    use Blameable;

    protected $fillable = [
        'transaction_number', 'reservation_id', 'type', 'amount',
        'payment_method', 'source_type', 'idempotency_key', 'notes', 'created_by',
    ];

    // Tipe pembayaran: dp, pelunasan, checkin_payment, additional, checkout_payment, refund, tambahan, ota_payment
    // Metode: cash, bank_transfer, credit_card, debit_card, tiket.com, traveloka.com, ota_payment, virtual_account, ewallet, qris

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->transaction_number)) {
                $model->transaction_number = 'TRX-'.strtoupper(uniqid());
            }
        });
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Tanggal pembayaran menurut periode bisnis (mengikuti cutoff night audit).
     *
     * Transaksi jam 00:00–cutoff (mis. 01:02) masuk periode bisnis hari sebelumnya,
     * jadi created_at (14/11 01:02) tampil sebagai 13/11. UI dan input tanggal
     * memakai tanggal bisnis ini supaya konsisten dengan laporan Night Audit.
     */
    public function businessDate(): Carbon
    {
        return Carbon::parse(
            NightAuditLockService::businessDateOf($this->created_at).' '.$this->created_at->format('H:i:s')
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
