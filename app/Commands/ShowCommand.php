<?php

namespace App\Commands;

use App\Models\Task;
use App\Commands\Concerns\EnsuresInstallation;
use LaravelZero\Framework\Commands\Command;

class ShowCommand extends Command
{
    use EnsuresInstallation;

    protected $signature = 'task:show {id : The task ID}';

    protected $description = 'Show detailed information about a task';

    public function handle(): void
    {
        $this->ensureInstalled();

        $task = Task::with(['status', 'category', 'priority'])->find($this->argument('id'));

        if (! $task) {
            $this->error("  Task #{$this->argument('id')} not found.");
            return;
        }

        $this->newLine();
        $this->info("  📋 Task #{$task->id}: {$task->name}");
        $this->line('  ' . str_repeat('─', 50));

        if ($task->description) {
            $this->newLine();
            $this->line("  {$task->description}");
        }

        $this->newLine();

        $details = [
            ['Status', $task->status?->name ?? '-'],
            ['Priority', $task->priority?->name ?? '-'],
            ['Category', $task->category?->name ?? '-'],
            ['Deadline', $task->deadline?->format('Y-m-d') ?? '-'],
            ['Expected Date', $task->expected_date?->format('Y-m-d') ?? '-'],
            ['Created', $task->created_at?->format('Y-m-d H:i')],
        ];

        if ($task->completed_at) {
            $details[] = ['Completed', $task->completed_at->format('Y-m-d H:i')];
        }

        if ($task->isOverdue()) {
            $details[] = ['', '<fg=red>⚠ OVERDUE</>'];
        }

        foreach ($details as [$label, $value]) {
            if ($label) {
                $this->line(sprintf('  <fg=cyan>%-15s</> %s', $label, $value));
            } else {
                $this->line("  {$value}");
            }
        }

        $this->newLine();
    }
}