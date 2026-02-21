<?php

namespace App\Commands;

use App\Enums\StatusType;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use LaravelZero\Framework\Commands\Command;

class AddCommand extends Command
{
    protected $signature = 'task:add
        {--name= : Task name}
        {--description= : Task description}
        {--category= : Category name}
        {--priority= : Priority name}
        {--deadline= : Deadline (YYYY-MM-DD)}
        {--expected-date= : Expected date (YYYY-MM-DD)}';

    protected $description = 'Create a new task';

    public function handle(): void
    {
        $this->ensureInstalled();

        $name = $this->option('name') ?: $this->ask('Task name');

        if (empty($name)) {
            $this->error('  Task name is required.');
            return;
        }

        $description = $this->option('description') ?: $this->ask('Description (optional)');

        // Category
        $categories = TaskCategory::all();
        $categoryId = null;

        if ($categories->isNotEmpty()) {
            $categoryOption = $this->option('category');

            if ($categoryOption) {
                $category = TaskCategory::where('name', $categoryOption)->first();
                $categoryId = $category?->id;
            } else {
                $categoryNames = $categories->pluck('name')->toArray();
                $categoryNames[] = 'None';
                $chosen = $this->choice('Category', $categoryNames, count($categoryNames) - 1);

                if ($chosen !== 'None') {
                    $categoryId = $categories->where('name', $chosen)->first()?->id;
                }
            }
        }

        // Priority
        $priorities = TaskPriority::orderBy('level')->get();
        $priorityOption = $this->option('priority');

        if ($priorityOption) {
            $priority = TaskPriority::where('name', $priorityOption)->first();
            $priorityId = $priority?->id ?? $priorities->first()?->id;
        } else {
            $priorityNames = $priorities->pluck('name')->toArray();
            $defaultIndex = (int) floor(count($priorityNames) / 2); // default to middle
            $chosen = $this->choice('Priority', $priorityNames, $defaultIndex);
            $priorityId = $priorities->where('name', $chosen)->first()?->id;
        }

        // Status — always starts in Inbox (GTD: capture first)
        $inboxStatus = TaskStatus::where('type', StatusType::Inbox)->first();
        $statusId = $inboxStatus?->id ?? TaskStatus::first()?->id;

        // Dates
        $deadline = $this->option('deadline') ?: $this->ask('Deadline (YYYY-MM-DD, optional)');
        $expectedDate = $this->option('expected-date') ?: $this->ask('Expected date (YYYY-MM-DD, optional)');

        $task = Task::create([
            'name' => $name,
            'description' => $description ?: null,
            'task_category_id' => $categoryId,
            'task_priority_id' => $priorityId,
            'task_status_id' => $statusId,
            'deadline' => $deadline ?: null,
            'expected_date' => $expectedDate ?: null,
        ]);

        $this->newLine();
        $this->info("  ✅ Task #{$task->id} created: {$task->name}");
        $statusName = $inboxStatus?->name ?? 'N/A';
        $priorityName = $task->priority?->name ?? 'N/A';
        $this->line("  Status: {$statusName} | Priority: {$priorityName}");
        $this->newLine();
    }

    private function ensureInstalled(): void
    {
        try {
            TaskStatus::count();
        } catch (\Exception $e) {
            $this->error('  Todo CLI is not set up yet. Run `todo task:install` first.');
            exit(1);
        }
    }
}