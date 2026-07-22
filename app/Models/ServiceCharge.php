<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Blameable;

class ServiceCharge extends Model
{
    use Blameable;

    protected $table = 'service_charges';

    protected $fillable = [
        'charge_number', 'reservation_id', 'guest_id', 'service_name',
        'description', 'amount', 'quantity', 'total_amount',
        'charge_date', 'payment_method', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'charge_date' => 'date',
        'quantity' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->charge_number)) {
                $model->charge_number = 'SC-'.strtoupper(uniqid());
            }
        });
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
