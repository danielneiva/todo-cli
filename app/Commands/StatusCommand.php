<?php

namespace App\Commands;

use App\Enums\StatusType;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Commands\Concerns\EnsuresInstallation;
use LaravelZero\Framework\Commands\Command;

class StatusCommand extends Command
{
    use EnsuresInstallation;

    protected $signature = 'task:status {id : The task ID} {status? : The new status name}';

    protected $description = 'Change a task\'s status';

    public function handle(): int
    {
        if (!$this->checkInstallation()) {
            return 1;
        }

        $task = Task::with('status')->find($this->argument('id'));

        if (!$task) {
            $this->error("  Task #{$this->argument('id')} not found.");
            return 1;
        }

        $this->info("  Task #{$task->id}: {$task->name}");
        $this->line("  Current status: <fg=cyan>{$task->status->name}</>");
        $this->newLine();

        $statusName = $this->argument('status');
        $newStatus = null;

        if ($statusName) {
            $newStatus = TaskStatus::where('name', 'like', "%{$statusName}%")->first();
            if (!$newStatus) {
                $this->error("  Status \"{$statusName}\" not found.");
                $this->line('  Available statuses: ' . TaskStatus::pluck('name')->implode(', '));
                return 1;
            }
        }
        else {
            $statuses = TaskStatus::all();
            $statusNames = $statuses->pluck('name')->toArray();
            $chosen = $this->choice('Move to status', $statusNames);
            $newStatus = $statuses->where('name', $chosen)->first();
        }

        $task->task_status_id = $newStatus->id;

        // Auto-set completed_at when moving to done
        if ($newStatus->type === StatusType::Done) {
            $task->completed_at = now();
        }
        elseif ($task->completed_at !== null) {
            // Clear completed_at if moving away from done
            $task->completed_at = null;
        }

        $task->save();

        $this->info("  ✅ Status changed to: {$newStatus->name}");

        if ($newStatus->type === StatusType::Done) {
            $this->line("  🎉 Task completed at " . $task->completed_at->format('Y-m-d H:i'));
        }

        $this->newLine();

        return 0;
    }
}