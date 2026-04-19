<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'assigned_user_id',
        'priority',
        'status',
        'corrective_action',
    ];

    protected function casts(): array
    {
        return [
            'status'   => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'date',
        ];
    }

    // Relationships

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(TaskActivityLog::class);
    }

    // Scopes

    public function scopePending(Builder $query): void
    {
        $query->where('status', TaskStatus::Pending);
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', TaskStatus::Completed);
    }

    public function scopeNonCompliant(Builder $query): void
    {
        $query->where('status', TaskStatus::NonCompliant);
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->where('due_date', '<', today())
              ->where('status', TaskStatus::Pending);
    }

    public function scopeDueToday(Builder $query): void
    {
        $query->whereDate('due_date', today());
    }

    public function scopeAssignedTo(Builder $query, int $userId): void
    {
        $query->where('assigned_user_id', $userId);
    }
}
