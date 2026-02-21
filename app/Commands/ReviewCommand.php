<?php

namespace App\Commands;

use App\Enums\StatusType;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Commands\Concerns\EnsuresInstallation;
use LaravelZero\Framework\Commands\Command;

class ReviewCommand extends Command
{
    use EnsuresInstallation;

    protected $signature = 'task:review';

    protected $description = 'Interactive weekly review — process inbox, review active tasks, handle overdue items';

    public function handle(): int
    {
        if (!$this->checkInstallation()) {
            return 1;
        }

        $this->title('🔄 Weekly Review Process');
        $this->newLine();
        $this->info('  🔄 Weekly Review');
        $this->line('  ' . str_repeat('─', 50));
        $this->newLine();

        $this->processInbox();
        $this->reviewActiveTasks();
        $this->handleOverdue();

        $this->newLine();
        $this->info('  ✅ Review complete!');
        $this->newLine();

        return 0;
    }

    private function processInbox(): void
    {
        $inboxStatuses = TaskStatus::where('type', StatusType::Inbox)->pluck('id');
        $tasks = Task::with(['priority', 'category'])
            ->whereIn('task_status_id', $inboxStatuses)
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('  📭 Inbox is empty — nothing to process.');
            $this->newLine();
            return;
        }

        $this->info("  📥 Step 1: Process Inbox ({$tasks->count()} items)");
        $this->newLine();

        $statuses = TaskStatus::all();
        $statusNames = $statuses->pluck('name')->toArray();
        $statusNames[] = 'Skip';

        foreach ($tasks as $task) {
            $this->line("  <fg=white;options=bold>#{$task->id}</> {$task->name}");

            if ($task->description) {
                $this->line("    <fg=gray>{$task->description}</>");
            }

            $chosen = $this->choice("  Move to", $statusNames, count($statusNames) - 1);

            if ($chosen === 'Skip') {
                $this->line('    → Skipped');
                continue;
            }

            $newStatus = $statuses->where('name', $chosen)->first();
            $task->task_status_id = $newStatus->id;

            if ($newStatus->type === StatusType::Done) {
                $task->completed_at = now();
            }

            $task->save();
            $this->line("    → Moved to <fg=green>{$newStatus->name}</>");
        }

        $this->newLine();
    }

    private function reviewActiveTasks(): void
    {
        $activeStatuses = TaskStatus::where('type', StatusType::Active)->pluck('id');
        $tasks = Task::with(['status', 'priority', 'category'])
            ->whereIn('task_status_id', $activeStatuses)
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('  📋 No active tasks to review.');
            $this->newLine();
            return;
        }

        $this->info("  📋 Step 2: Review Active Tasks ({$tasks->count()} items)");
        $this->newLine();

        $statuses = TaskStatus::all();
        $statusNames = $statuses->pluck('name')->toArray();
        $statusNames[] = 'Keep as is';

        foreach ($tasks as $task) {
            $statusLabel = $task->status->name;
            $priorityLabel = $task->priority->name;
            $this->line("  <fg=white;options=bold>#{$task->id}</> {$task->name} <fg=gray>[{$statusLabel} | {$priorityLabel}]</>");

            if ($task->deadline) {
                $deadlineStr = $task->deadline->format('Y-m-d');
                if ($task->isOverdue()) {
                    $this->line("    <fg=red>⚠ Overdue: {$deadlineStr}</>");
                }
                else {
                    $this->line("    <fg=gray>Deadline: {$deadlineStr}</>");
                }
            }

            $chosen = $this->choice("  Action", $statusNames, count($statusNames) - 1);

            if ($chosen === 'Keep as is') {
                continue;
            }

            $newStatus = $statuses->where('name', $chosen)->first();
            $task->task_status_id = $newStatus->id;

            if ($newStatus->type === StatusType::Done) {
                $task->completed_at = now();
            }

            $task->save();
            $this->line("    → Moved to <fg=green>{$newStatus->name}</>");
        }

        $this->newLine();
    }

    private function handleOverdue(): void
    {
        $activeStatusIds = TaskStatus::whereIn('type', [
            StatusType::Inbox->value,
            StatusType::Active->value,
        ])->pluck('id');

        $tasks = Task::with(['status', 'priority', 'category'])
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->toDateString())
            ->whereIn('task_status_id', $activeStatusIds)
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('  ⏰ No overdue tasks.');
            return;
        }

        $this->info("  ⏰ Step 3: Overdue Tasks ({$tasks->count()} items)");
        $this->newLine();

        foreach ($tasks as $task) {
            $days = now()->diffInDays($task->deadline);
            $this->line("  <fg=red;options=bold>#{$task->id}</> {$task->name} <fg=red>(overdue by {$days} days)</>");

            $action = $this->choice('  Action', [
                'Reschedule',
                'Mark as done',
                'Cancel',
                'Skip',
            ], 0);

            match ($action) {
                    'Reschedule' => $this->rescheduleTask($task),
                    'Mark as done' => $this->completeTask($task),
                    'Cancel' => $this->cancelTask($task),
                    default => $this->line('    → Skipped'),
                };
        }
    }

    private function rescheduleTask(Task $task): void
    {
        $newDeadline = $this->ask('  New deadline (YYYY-MM-DD)');
        if ($newDeadline) {
            $task->deadline = $newDeadline;
            $task->save();
            $this->line("    → Rescheduled to {$newDeadline}");
        }
    }

    private function completeTask(Task $task): void
    {
        $doneStatus = TaskStatus::where('type', StatusType::Done)->first();
        if ($doneStatus) {
            $task->task_status_id = $doneStatus->id;
            $task->completed_at = now();
            $task->save();
            $this->line('    → Marked as done');
        }
    }

    private function cancelTask(Task $task): void
    {
        $cancelledStatus = TaskStatus::where('type', StatusType::Cancelled)->first();
        if ($cancelledStatus) {
            $task->task_status_id = $cancelledStatus->id;
            $task->save();
            $this->line('    → Cancelled');
        }
    }
}