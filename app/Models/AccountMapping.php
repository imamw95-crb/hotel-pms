<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountMapping extends Model
{
    public const GROUP_PAYMENT_METHOD = 'payment_method';

    public const GROUP_REVENUE = 'revenue';

    public const GROUP_EXPENSE = 'expense';

    protected $fillable = ['group', 'key', 'label', 'account_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Akun yang dipetakan untuk sebuah grup & key (mengembalikan model atau null).
     */
    public static function resolve(string $group, ?string $key): ?Account
    {
        if ($key === null || $key === '') {
            return null;
        }

        return static::with('account')
            ->where('group', $group)
            ->where('key', $key)
            ->where('is_active', true)
            ->first()?->account;
    }
}
