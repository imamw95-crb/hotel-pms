<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_group_id',
        'reservation_number', 'ota_reservation_number', 'ota_source', 'ota_payment_status', 'ota_paid_amount',
        'room_id', 'guest_id', 'check_in', 'check_out',
        'number_of_cards', 'status', 'total_amount', 'paid_amount', 'paid_date', 'payment_method', 'notes', 'created_by',
        'custom_room_rate', 'include_breakfast',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'paid_date' => 'datetime',
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
