<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait Blameable
{
    /**
     * Boot the Blameable trait.
     * Automatically sets created_by on creating and updating events.
     */
    protected static function bootBlameable(): void
    {
        static::creating(function ($model) {
            $model->created_by = Auth::id() ?? 1;
        });

        static::updating(function ($model) {
            $model->created_by = Auth::id() ?? 1;
        });
    }
}
