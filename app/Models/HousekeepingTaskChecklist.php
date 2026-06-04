<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HousekeepingTaskChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'housekeeping_task_id',
        'item_name',
        'is_checked',
        'checked_by',
        'checked_at',
        'sort_order',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function housekeepingTask(): BelongsTo
    {
        return $this->belongsTo(HousekeepingTask::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
