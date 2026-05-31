<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'notes',
        'created_by',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Task type labels in Indonesian
     */
    const TASK_TYPES = [
        'cleaning'    => 'Pembersihan Reguler',
        'deep_clean'  => 'Pembersihan Mendalam',
        'maintenance' => 'Perbaikan/Maintenance',
        'inspection'  => 'Inspeksi Kamar',
        'turndown'    => 'Turndown Service',
    ];

    /**
     * Priority labels in Indonesian
     */
    const PRIORITIES = [
        'low'    => 'Rendah',
        'normal' => 'Normal',
        'high'   => 'Tinggi',
        'urgent' => 'Urgent',
    ];

    /**
     * Status labels in Indonesian
     */
    const STATUSES = [
        'pending'     => 'Menunggu',
        'in_progress' => 'Sedang Dikerjakan',
        'completed'   => 'Selesai',
        'cancelled'   => 'Dibatalkan',
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
            'high'   => 'bg-orange-100 text-orange-800 border-orange-300',
            'normal' => 'bg-blue-100 text-blue-800 border-blue-300',
            'low'    => 'bg-gray-100 text-gray-600 border-gray-300',
            default  => 'bg-gray-100 text-gray-600 border-gray-300',
        };
    }

    /**
     * Get color class for status badge
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'in_progress' => 'bg-blue-100 text-blue-800 border-blue-300',
            'completed'   => 'bg-green-100 text-green-800 border-green-300',
            'cancelled'   => 'bg-gray-100 text-gray-500 border-gray-300',
            default       => 'bg-gray-100 text-gray-600 border-gray-300',
        };
    }

    /**
     * Get icon for task type
     */
    public function getTaskTypeIconAttribute(): string
    {
        return match ($this->task_type) {
            'cleaning'    => 'fa-broom',
            'deep_clean'  => 'fa-spray-can',
            'maintenance' => 'fa-tools',
            'inspection'  => 'fa-clipboard-check',
            'turndown'    => 'fa-moon',
            default       => 'fa-tasks',
        };
    }
}
