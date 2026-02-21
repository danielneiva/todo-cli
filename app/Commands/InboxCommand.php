<?php

namespace App\Commands;

use App\Enums\StatusType;
use App\Models\Task;
use App\Models\TaskStatus;
use LaravelZero\Framework\Commands\Command;

class InboxCommand extends Command
{
    protected $signature = 'task:inbox';

    protected $description = 'Show all unprocessed tasks in your inbox';

    public function handle(): void
    {
        $inboxStatuses = TaskStatus::where('type', StatusType::Inbox)->pluck('id');

        if ($inboxStatuses->isEmpty()) {
            $this->warn('  No inbox status found. Run `todo task:install` to set up.');
            return;
        }

        $tasks = Task::with(['priority', 'category'])
            ->whereIn('task_status_id', $inboxStatuses)
            ->orderByDesc('created_at')
            ->get();

        if ($tasks->isEmpty()) {
            $this->newLine();
            $this->info('  📭 Inbox is empty — you\'re all caught up!');
            $this->newLine();
            return;
        }

        $this->newLine();
        $this->info("  📥 Inbox ({$tasks->count()} tasks)");
        $this->newLine();

        $rows = $tasks->map(fn (Task $task) => [
            $task->id,
            $task->name,
            $task->category?->name ?? '-',
            $task->priority?->name ?? '-',
            $task->deadline?->format('Y-m-d') ?? '-',
            $task->created_at?->format('Y-m-d'),
        ])->toArray();

        $this->table(
            ['ID', 'Name', 'Category', 'Priority', 'Deadline', 'Created'],
            $rows
        );

        $this->line('  Tip: Run <fg=cyan>todo task:status {id} {status}</> to process items.');
        $this->newLine();
    }
}