<?php

namespace App\Commands;

use App\Enums\StatusType;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Commands\Concerns\EnsuresInstallation;
use App\Models\TaskStatus;
use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    use EnsuresInstallation;

    protected $signature = 'task:list
        {--status= : Filter by status name}
        {--category= : Filter by category name}
        {--priority= : Filter by priority name}
        {--overdue : Show only overdue tasks}
        {--all : Include done and cancelled tasks}
        {--search= : Search in name and description}';

    protected $description = 'List and filter tasks';

    public function handle(): void
    {
        $this->ensureInstalled();

        $query = Task::with(['status', 'priority', 'category']);

        // Status filter
        if ($statusName = $this->option('status')) {
            $status = TaskStatus::where('name', 'like', "%{$statusName}%")->first();
            if ($status) {
                $query->where('task_status_id', $status->id);
            } else {
                $this->warn("  Status \"{$statusName}\" not found.");
                return;
            }
        } elseif (! $this->option('all')) {
            // By default, exclude done and cancelled
            $activeStatusIds = TaskStatus::whereIn('type', [
                StatusType::Inbox->value,
                StatusType::Active->value,
            ])->pluck('id');
            $query->whereIn('task_status_id', $activeStatusIds);
        }

        // Category filter
        if ($categoryName = $this->option('category')) {
            $category = TaskCategory::where('name', 'like', "%{$categoryName}%")->first();
            if ($category) {
                $query->where('task_category_id', $category->id);
            }
        }

        // Priority filter
        if ($priorityName = $this->option('priority')) {
            $priority = TaskPriority::where('name', 'like', "%{$priorityName}%")->first();
            if ($priority) {
                $query->where('task_priority_id', $priority->id);
            }
        }

        // Overdue filter
        if ($this->option('overdue')) {
            $activeStatusIds = TaskStatus::whereIn('type', [
                StatusType::Inbox->value,
                StatusType::Active->value,
            ])->pluck('id');

            $query->whereNotNull('deadline')
                  ->where('deadline', '<', now()->toDateString())
                  ->whereIn('task_status_id', $activeStatusIds);
        }

        // Search
        if ($search = $this->option('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->orderByDesc('task_priority_id')->get();

        if ($tasks->isEmpty()) {
            $this->info('  No tasks found.');
            return;
        }

        $rows = $tasks->map(function (Task $task) {
            $priorityName = $task->priority?->name ?? '-';
            $priorityLevel = $task->priority?->level ?? 0;

            // Color-code priority
            $priority = match (true) {
                $priorityLevel >= 30 => "<fg=red>{$priorityName}</>",
                $priorityLevel >= 20 => "<fg=yellow>{$priorityName}</>",
                $priorityLevel >= 10 => "<fg=cyan>{$priorityName}</>",
                default => "<fg=gray>{$priorityName}</>",
            };

            $deadline = $task->deadline?->format('Y-m-d') ?? '-';

            // Mark overdue
            if ($task->isOverdue()) {
                $deadline = "<fg=red>{$deadline} ⚠</>";
            }

            return [
                $task->id,
                $task->name,
                $task->category?->name ?? '-',
                $priority,
                $task->status?->name ?? '-',
                $deadline,
                $task->expected_date?->format('Y-m-d') ?? '-',
            ];
        })->toArray();

        $this->newLine();
        $this->table(
            ['ID', 'Name', 'Category', 'Priority', 'Status', 'Deadline', 'Expected'],
            $rows
        );
    }
}