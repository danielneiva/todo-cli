<?php

namespace App\Models;

use App\Enums\StatusType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $task_category_id
 * @property int $task_priority_id
 * @property int $task_status_id
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property \Illuminate\Support\Carbon|null $expected_date
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property TaskStatus $status
 * @property TaskPriority $priority
 * @property TaskCategory|null $category
 */
class Task extends Model
{
    protected $fillable = [
        'name',
        'description',
        'task_category_id',
        'task_priority_id',
        'task_status_id',
        'deadline',
        'expected_date',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'expected_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<TaskStatus, $this> */
    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class , 'task_status_id');
    }

    /** @return BelongsTo<TaskPriority, $this> */
    public function priority(): BelongsTo
    {
        return $this->belongsTo(TaskPriority::class , 'task_priority_id');
    }

    /** @return BelongsTo<TaskCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class , 'task_category_id');
    }

    /**
     * Check if the task is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->deadline !== null
            && $this->deadline->isPast()
            && $this->status->type->value !== 'done'
            && $this->status->type->value !== 'cancelled';
    }
}