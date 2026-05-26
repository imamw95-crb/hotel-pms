<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    protected $fillable = [
        'receipt_number', 'guest_id', 'reservation_id', 'number_of_cards',
        'nominal_per_card', 'total_amount', 'payment_method', 'notes', 'status', 'created_by',
    ];

    protected $casts = [
        'nominal_per_card' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->receipt_number)) {
                $model->receipt_number = 'DEP-' . strtoupper(uniqid());
            }
            // Auto-calculate total
            if (empty($model->total_amount)) {
                $model->total_amount = $model->number_of_cards * $model->nominal_per_card;
            }
        });
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
