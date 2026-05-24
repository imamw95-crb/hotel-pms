<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    protected $fillable = [
        'reservation_number', 'room_id', 'guest_id', 'check_in', 'check_out',
        'number_of_cards', 'status', 'total_amount', 'paid_amount', 'payment_method', 'notes', 'created_by'
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->reservation_number)) {
                $model->reservation_number = 'RES-' . strtoupper(uniqid());
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

    public function getRemainingPaymentAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getNightsAttribute()
    {
        return $this->check_in->diffInDays($this->check_out);
    }

    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'Pending',
            'checked_in' => 'Checked In',
            'checked_out' => 'Checked Out',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ][$this->status] ?? $this->status;
    }
}