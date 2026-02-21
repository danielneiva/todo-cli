<?php

namespace App\Commands;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Commands\Concerns\EnsuresInstallation;
use LaravelZero\Framework\Commands\Command;

class EditCommand extends Command
{
    use EnsuresInstallation;

    protected $signature = 'task:edit {id : The task ID}
        {--name= : New name}
        {--description= : New description}
        {--category= : New category name}
        {--priority= : New priority name}
        {--deadline= : New deadline (YYYY-MM-DD)}
        {--expected-date= : New expected date (YYYY-MM-DD)}';

    protected $description = 'Edit an existing task';

    public function handle(): int
    {
        if (! $this->checkInstallation()) {
            return 1;
        }

        $task = Task::with(['status', 'priority', 'category'])->find($this->argument('id'));

        if (! $task) {
            $this->error("  Task #{$this->argument('id')} not found.");
            return 1;
        }

        $this->info("  ✏️  Editing Task #{$task->id}: {$task->name}");
        $this->line('  Press Enter to keep the current value.');
        $this->newLine();

        $hasFlags = collect(['name', 'description', 'category', 'priority', 'deadline', 'expected-date'])
            ->contains(fn ($opt) => $this->option($opt) !== null);

        if ($hasFlags) {
            $this->applyFlags($task);
        } else {
            $this->interactiveEdit($task);
        }

        $task->save();

        $this->newLine();
        $this->info("  ✅ Task #{$task->id} updated.");
        $this->newLine();

        return 0;
    }

    private function applyFlags(Task $task): void
    {
        if ($name = $this->option('name')) {
            $task->name = $name;
        }

        if ($desc = $this->option('description')) {
            $task->description = $desc;
        }

        if ($catName = $this->option('category')) {
            $cat = TaskCategory::where('name', 'like', "%{$catName}%")->first();
            if ($cat) {
                $task->task_category_id = $cat->id;
            }
        }

        if ($priName = $this->option('priority')) {
            $pri = TaskPriority::where('name', 'like', "%{$priName}%")->first();
            if ($pri) {
                $task->task_priority_id = $pri->id;
            }
        }

        if ($deadline = $this->option('deadline')) {
            $task->deadline = $deadline === 'clear' ? null : $deadline;
        }

        if ($expectedDate = $this->option('expected-date')) {
            $task->expected_date = $expectedDate === 'clear' ? null : $expectedDate;
        }
    }

    private function interactiveEdit(Task $task): void
    {
        // Name
        $name = $this->ask("Name [{$task->name}]");
        if ($name) {
            $task->name = $name;
        }

        // Description
        $currentDesc = $task->description ?? 'none';
        $desc = $this->ask("Description [{$currentDesc}]");
        if ($desc) {
            $task->description = $desc === 'clear' ? null : $desc;
        }

        // Category
        $categories = TaskCategory::all();
        if ($categories->isNotEmpty()) {
            $currentCat = $task->category?->name ?? 'None';
            $catNames = $categories->pluck('name')->toArray();
            $catNames[] = 'None';
            $catNames[] = "Keep ({$currentCat})";

            $chosen = $this->choice('Category', $catNames, count($catNames) - 1);
            if ($chosen === 'None') {
                $task->task_category_id = null;
            } elseif (! str_starts_with($chosen, 'Keep')) {
                $task->task_category_id = $categories->where('name', $chosen)->first()?->id;
            }
        }

        // Priority
        $priorities = TaskPriority::orderBy('level')->get();
        $currentPri = $task->priority?->name ?? '-';
        $priNames = $priorities->pluck('name')->toArray();
        $priNames[] = "Keep ({$currentPri})";

        $chosen = $this->choice('Priority', $priNames, count($priNames) - 1);
        if (! str_starts_with($chosen, 'Keep')) {
            $task->task_priority_id = $priorities->where('name', $chosen)->first()?->id;
        }

        // Deadline
        $currentDeadline = $task->deadline?->format('Y-m-d') ?? 'none';
        $deadline = $this->ask("Deadline [{$currentDeadline}] (YYYY-MM-DD or 'clear')");
        if ($deadline) {
            $task->deadline = $deadline === 'clear' ? null : $deadline;
        }

        // Expected date
        $currentExpected = $task->expected_date?->format('Y-m-d') ?? 'none';
        $expected = $this->ask("Expected date [{$currentExpected}] (YYYY-MM-DD or 'clear')");
        if ($expected) {
            $task->expected_date = $expected === 'clear' ? null : $expected;
        }
    }
}