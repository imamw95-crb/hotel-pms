<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HousekeepingTask extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'room_id',
        'task_type',
        'priority',
        'description',
        'status',
        'assigned_to',
        'completed_by',
        'completed_at',
        'started_at',
        'duration_minutes',
        'photo_before',
        'photo_after',
        'room_condition_before',
        'room_condition_after',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /**
     * Task type labels in Indonesian
     */
    const TASK_TYPES = [
        'cleaning' => 'Pembersihan Reguler',
        'deep_clean' => 'Pembersihan Mendalam',
        'maintenance' => 'Perbaikan/Maintenance',
        'inspection' => 'Inspeksi Kamar',
        'turndown' => 'Turndown Service',
    ];

    /**
     * Priority labels in Indonesian
     */
    const PRIORITIES = [
        'low' => 'Rendah',
        'normal' => 'Normal',
        'high' => 'Tinggi',
        'urgent' => 'Urgent',
    ];

    /**
     * Status labels in Indonesian
     */
    const STATUSES = [
        'pending' => 'Menunggu',
        'in_progress' => 'Sedang Dikerjakan',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    // ─── Relasi ───────────────────────────────────────────────────────

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(HousekeepingTaskChecklist::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(HousekeepingTaskLog::class, 'housekeeping_task_id');
    }

    public function lostFound(): HasMany
    {
        return $this->hasMany(LostFound::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getTaskTypeLabelAttribute(): string
    {
        return self::TASK_TYPES[$this->task_type] ?? $this->task_type;
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get color class for priority badge
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-red-100 text-red-800 border-red-300',
            'high' => 'bg-orange-100 text-orange-800 border-orange-300',
            'normal' => 'bg-blue-100 text-blue-800 border-blue-300',
            'low' => 'bg-gray-100 text-gray-600 border-gray-300',
            default => 'bg-gray-100 text-gray-600 border-gray-300',
        };
    }

    /**
     * Get color class for status badge
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'in_progress' => 'bg-blue-100 text-blue-800 border-blue-300',
            'completed' => 'bg-green-100 text-green-800 border-green-300',
            'cancelled' => 'bg-gray-100 text-gray-500 border-gray-300',
            default => 'bg-gray-100 text-gray-600 border-gray-300',
        };
    }

    /**
     * Get icon for task type
     */
    public function getTaskTypeIconAttribute(): string
    {
        return match ($this->task_type) {
            'cleaning' => 'fa-broom',
            'deep_clean' => 'fa-spray-can',
            'maintenance' => 'fa-tools',
            'inspection' => 'fa-clipboard-check',
            'turndown' => 'fa-moon',
            default => 'fa-tasks',
        };
    }

    /**
     * Get human-readable duration label.
     */
    public function getDurationLabelAttribute(): ?string
    {
        if (! $this->duration_minutes) {
            return null;
        }

        $hours = intdiv($this->duration_minutes, 60);
        $mins = $this->duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}j {$mins}m";
        }

        return "{$mins} menit";
    }

    /**
     * Get full URL for photo before.
     */
    public function getPhotoBeforeUrlAttribute(): ?string
    {
        return $this->photo_before ? asset('storage/'.$this->photo_before) : null;
    }

    /**
     * Get full URL for photo after.
     */
    public function getPhotoAfterUrlAttribute(): ?string
    {
        return $this->photo_after ? asset('storage/'.$this->photo_after) : null;
    }

    /**
     * Get formatted started_at.
     */
    public function getStartedAtFormattedAttribute(): ?string
    {
        return $this->started_at?->format('d/m/Y H:i');
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    /**
     * Scope: only urgent tasks that are still active.
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent')
            ->whereIn('status', ['pending', 'in_progress']);
    }

    /**
     * Scope: tasks assigned to a specific staff.
     */
    public function scopeByStaff($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope: tasks created today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    /**
     * Scope: tasks that are overdue (in_progress for more than 4 hours).
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'in_progress')
            ->where('started_at', '<', Carbon::now()->subHours(4));
    }

    /**
     * Scope: active tasks (pending or in_progress).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    /**
     * Scope: completed tasks within a date range.
     */
    public function scopeCompletedBetween($query, $start, $end)
    {
        return $query->where('status', 'completed')
            ->whereBetween('completed_at', [$start, $end]);
    }
}
