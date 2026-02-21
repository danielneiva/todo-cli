<?php

namespace App\Commands;

use App\Enums\StatusType;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\Schema;
use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    protected $signature = 'task:install';

    protected $description = 'Set up your Todo CLI — configure statuses, priorities, and categories';

    private array $defaultStatuses = [
        ['name' => 'Inbox', 'type' => 'inbox'],
        ['name' => 'Next Action', 'type' => 'active'],
        ['name' => 'Waiting For', 'type' => 'active'],
        ['name' => 'Someday/Maybe', 'type' => 'active'],
        ['name' => 'Done', 'type' => 'done'],
        ['name' => 'Cancelled', 'type' => 'cancelled'],
    ];

    private array $defaultPriorities = [
        ['name' => 'Low', 'level' => 0],
        ['name' => 'Medium', 'level' => 10],
        ['name' => 'High', 'level' => 20],
        ['name' => 'Urgent', 'level' => 30],
    ];

    private array $defaultCategories = [
        'Personal',
        'Work',
        'Learning',
    ];

    public function handle(): void
    {
        $this->info('');
        $this->info('  🚀 Welcome to Todo CLI!');
        $this->info('  Let\'s set things up.');
        $this->info('');

        // Ensure database file exists
        $dbPath = config('database.connections.sqlite.database');
        $dbDir = dirname($dbPath);

        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        if (!file_exists($dbPath)) {
            touch($dbPath);
        }

        // Run migrations
        $this->task('Running database migrations', function () {
            $this->callSilently('migrate', ['--force' => true]);
        });

        // Check if already initialized
        if (TaskStatus::count() > 0) {
            if (!$this->confirm('Your Todo CLI is already set up. Do you want to reconfigure?', false)) {
                $this->info('  ✅ All good! Run `todo task:add` to create your first task.');
                return;
            }

            // Clear existing config data (but not tasks)
            TaskStatus::query()->delete();
            TaskPriority::query()->delete();
            TaskCategory::query()->delete();
        }

        $this->setupStatuses();
        $this->setupPriorities();
        $this->setupCategories();

        $this->newLine();
        $this->info('  ✅ Todo CLI is ready! Run `todo task:add` to create your first task.');
        $this->info('');
    }

    private function setupStatuses(): void
    {
        $this->newLine();
        $this->info('  📋 Task Statuses (GTD Workflow)');
        $this->line('  Defaults: Inbox, Next Action, Waiting For, Someday/Maybe, Done, Cancelled');
        $this->newLine();

        $useDefaults = $this->choice(
            'How would you like to set up statuses?',
        ['Use GTD defaults', 'Enter my own'],
            0
        );

        if ($useDefaults === 'Use GTD defaults') {
            foreach ($this->defaultStatuses as $status) {
                TaskStatus::create($status);
            }
            $this->info('  ✓ Default statuses created.');
        }
        else {
            $this->newLine();
            $this->line('  Enter your statuses. For each, you\'ll assign a type:');
            $this->line('    <fg=cyan>inbox</> — Unprocessed items (capture bucket)');
            $this->line('    <fg=green>active</> — Items you\'re working on or tracking');
            $this->line('    <fg=blue>done</> — Completed items');
            $this->line('    <fg=red>cancelled</> — Dropped items');
            $this->newLine();

            $input = $this->ask('Enter your statuses (comma-separated)');
            $names = array_map('trim', explode(',', $input));

            foreach ($names as $name) {
                if (empty($name)) {
                    continue;
                }

                $type = $this->choice(
                    "  Type for \"$name\"",
                ['inbox', 'active', 'done', 'cancelled'],
                    1
                );

                TaskStatus::create(['name' => $name, 'type' => $type]);
            }

            $this->info('  ✓ Custom statuses created.');
        }
    }

    private function setupPriorities(): void
    {
        $this->newLine();
        $this->info('  ⚡ Task Priorities');
        $this->line('  Defaults: Low, Medium, High, Urgent');
        $this->newLine();

        $useDefaults = $this->choice(
            'How would you like to set up priorities?',
        ['Use defaults', 'Enter my own'],
            0
        );

        if ($useDefaults === 'Use defaults') {
            foreach ($this->defaultPriorities as $priority) {
                TaskPriority::create($priority);
            }
            $this->info('  ✓ Default priorities created.');
        }
        else {
            $input = $this->ask('Enter your priorities (comma-separated, from lowest to highest)');
            $names = array_map('trim', explode(',', $input));

            foreach ($names as $index => $name) {
                if (empty($name)) {
                    continue;
                }
                TaskPriority::create([
                    'name' => $name,
                    'level' => $index * 10,
                ]);
            }

            $this->info('  ✓ Custom priorities created.');
        }
    }

    private function setupCategories(): void
    {
        $this->newLine();
        $this->info('  🏷️  Task Categories');
        $this->line('  Defaults: Personal, Work, Learning');
        $this->newLine();

        $useDefaults = $this->choice(
            'How would you like to set up categories?',
        ['Use defaults', 'Enter my own'],
            0
        );

        if ($useDefaults === 'Use defaults') {
            foreach ($this->defaultCategories as $name) {
                TaskCategory::create(['name' => $name]);
            }
            $this->info('  ✓ Default categories created.');
        }
        else {
            $input = $this->ask('Enter your categories (comma-separated)');
            $names = array_map('trim', explode(',', $input));

            foreach ($names as $name) {
                if (empty($name)) {
                    continue;
                }
                TaskCategory::create(['name' => $name]);
            }

            $this->info('  ✓ Custom categories created.');
        }
    }
}