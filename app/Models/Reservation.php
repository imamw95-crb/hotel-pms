<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Blameable;

class Reservation extends Model
{
    use HasFactory;
    use Blameable;

    // ========== STATUS CONSTANTS ==========
    /** Status yang menganggap kamar masih terpakai (mencegah double-booking) */
    public const ACTIVE_STATUSES = ['pending', 'menunggu_pembayaran', 'checked_in'];

    /** Status yang bisa menerima deposit / service charge */
    public const PENDING_STATUSES = ['pending', 'menunggu_pembayaran'];

    /** Status yang boleh dipindah kamar / extend / diubah */
    public const CHANGEABLE_STATUSES = ['pending', 'menunggu_pembayaran', 'checked_in'];

    protected $fillable = [
        'booking_group_id',
        'reservation_number', 'ota_reservation_number', 'ota_source', 'ota_payment_status', 'ota_paid_amount',
        'room_id', 'guest_id', 'check_in', 'check_out',
        'number_of_cards', 'status', 'total_amount', 'paid_amount', 'paid_date', 'payment_method', 'notes', 'created_by',
        'custom_room_rate', 'include_breakfast', 'room_type_name',
        'checked_in_by', 'checked_out_by', 'checked_in_at', 'checked_out_at',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'paid_date' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'custom_room_rate' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'include_breakfast' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->reservation_number)) {
                $model->reservation_number = 'RES-'.strtoupper(uniqid());
            }
        });
        static::created(function ($model) {
            if (empty($model->invoice_signature)) {
                try {
                    $model->invoice_signature = app(\App\Services\InvoiceSignatureService::class)->generate($model);
                    $model->saveQuietly();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Gagal generate invoice_signature: '.$e->getMessage());
                }
            }
        });
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function serviceCharges(): HasMany
    {
        return $this->hasMany(ServiceCharge::class);
    }

    public function restoTransactions(): HasMany
    {
        return $this->hasMany(RestoTransaction::class);
    }

    /**
     * Tipe transaksi yang BUKAN pembayaran tagihan kamar:
     * - additional  : other revenue / service charge (pendapatan terpisah)
     * - adjustment   : jejak perubahan tanggal (nominal 0)
     */
    public const NON_BILL_PAYMENT_TYPES = ['additional', 'adjustment'];

    /**
     * Total pembayaran yang benar-benar tercatat di Riwayat Pembayaran.
     *
     * Tabel transactions adalah sumber kebenaran nominal yang diterima hotel,
     * jadi paid_amount tidak boleh dihitung manual di tiap controller.
     */
    public function recordedPaidAmount(): float
    {
        return (float) $this->transactions()
            ->whereNotIn('type', self::NON_BILL_PAYMENT_TYPES)
            ->sum('amount');
    }

    /**
     * Bagian paid_amount yang belum punya baris transaksi (input manual lama,
     * sebelum fitur Riwayat Pembayaran ada). Harus diambil SEBELUM transaksi
     * baru dibuat, supaya nominal historis tidak hilang saat rekonsiliasi.
     */
    public function unrecordedPaidAmount(): float
    {
        return max(0, (float) $this->paid_amount - $this->recordedPaidAmount());
    }

    /**
     * Samakan paid_amount dengan total transaksi pembayaran (+ nominal historis).
     *
     * Dipanggil setelah setiap perubahan pembayaran supaya "Total Dibayar" di
     * halaman reservasi selalu sama dengan jumlah baris di Riwayat Pembayaran.
     */
    public function syncPaidAmount(float $legacyOffset = 0.0): float
    {
        $this->paid_amount = $this->recordedPaidAmount() + $legacyOffset;

        return (float) $this->paid_amount;
    }

    public function getRemainingPaymentAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getNightsAttribute()
    {
        // Standard hotel: check-in jam 14:00, check-out jam 12:00
        // Lama inap ditampilkan per malam, minimal 1 malam jika menginap
        // Contoh: CI 30/05 14:00 CO 31/05 12:00 = 1 malam
        //         CI 30/05 14:00 CO 01/06 12:00 = 1 malam (diffInDays=1)
        //         CI 30/05 14:00 CO 02/06 12:00 = 2 malam (diffInDays=2)
        if (! $this->check_in || ! $this->check_out) {
            return 0;
        }

        return max(1, (int) $this->check_in->startOfDay()->diffInDays($this->check_out->startOfDay()));
    }

    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'Pending',
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'checked_in' => 'Checked In',
            'checked_out' => 'Checked Out',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ][$this->status] ?? $this->status;
    }
}
