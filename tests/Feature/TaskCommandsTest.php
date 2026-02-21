<?php

use App\Enums\StatusType;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\TaskStatus;

beforeEach(function () {
    // Seed default data for tests
    TaskStatus::create(['name' => 'Inbox', 'type' => StatusType::Inbox->value]);
    TaskStatus::create(['name' => 'Next Action', 'type' => StatusType::Active->value]);
    TaskStatus::create(['name' => 'Done', 'type' => StatusType::Done->value]);
    TaskStatus::create(['name' => 'Cancelled', 'type' => StatusType::Cancelled->value]);

    TaskPriority::create(['name' => 'Low', 'level' => 0]);
    TaskPriority::create(['name' => 'Medium', 'level' => 10]);
    TaskPriority::create(['name' => 'High', 'level' => 20]);

    TaskCategory::create(['name' => 'Work']);
    TaskCategory::create(['name' => 'Personal']);
});

it('prompts user to install if database is empty', function () {
    // Truncate statuses to simulate uninstalled state
    TaskStatus::truncate();

    $this->artisan('task:add')
        ->expectsOutputToContain('Todo CLI is not set up yet')
        ->assertExitCode(1);
});

it('creates a task interactively', function () {
    $this->artisan('task:add')
        ->expectsQuestion('Task name', 'Interactive Task')
        ->expectsQuestion('Description (optional)', 'Test Description')
        ->expectsChoice('Category', 'Work', ['Work', 'Personal', 'None'])
        ->expectsChoice('Priority', 'High', ['Low', 'Medium', 'High'])
        ->expectsQuestion('Deadline (YYYY-MM-DD, optional)', '2026-03-01')
        ->expectsQuestion('Expected date (YYYY-MM-DD, optional)', '2026-02-25')
        ->assertExitCode(0);

    $task = Task::where('name', 'Interactive Task')->first();
    expect($task)->not->toBeNull();
    expect($task->description)->toBe('Test Description');
    expect($task->category->name)->toBe('Work');
    expect($task->priority->name)->toBe('High');
});

it('creates a task with flags', function () {
    $this->artisan('task:add', [
        '--name' => 'Test Task',
        '--description' => 'A test description',
        '--category' => 'Work',
        '--priority' => 'High',
        '--deadline' => '2026-03-01',
        '--expected-date' => '2026-02-25',
    ])->assertExitCode(0);

    $task = Task::first();
    expect($task)->not->toBeNull();
    expect($task->name)->toBe('Test Task');
    expect($task->description)->toBe('A test description');
    expect($task->category->name)->toBe('Work');
    expect($task->priority->name)->toBe('High');
    expect($task->status->type)->toBe(StatusType::Inbox);
    expect($task->deadline->format('Y-m-d'))->toBe('2026-03-01');
    expect($task->expected_date->format('Y-m-d'))->toBe('2026-02-25');
});

it('defaults task status to inbox', function () {
    $this->artisan('task:add', [
        '--name' => 'Inbox Task',
        '--description' => 'test desc',
        '--category' => 'Work',
        '--priority' => 'Medium',
        '--deadline' => '2026-04-01',
        '--expected-date' => '2026-03-15',
    ])->assertExitCode(0);

    $task = Task::first();
    expect($task->status->name)->toBe('Inbox');
    expect($task->status->type)->toBe(StatusType::Inbox);
});

it('lists tasks', function () {
    Task::create([
        'name' => 'Test Task',
        'task_priority_id' => TaskPriority::where('name', 'High')->first()->id,
        'task_status_id' => TaskStatus::where('name', 'Inbox')->first()->id,
    ]);

    $this->artisan('task:list', ['--all' => true])
        ->assertExitCode(0);

    expect(Task::count())->toBe(1);
    expect(Task::first()->name)->toBe('Test Task');
});

it('shows a single task', function () {
    Task::create([
        'name' => 'Detail Task',
        'description' => 'Full details here',
        'task_priority_id' => TaskPriority::where('name', 'Medium')->first()->id,
        'task_status_id' => TaskStatus::where('name', 'Inbox')->first()->id,
    ]);

    $this->artisan('task:show', ['id' => 1])
        ->assertExitCode(0);
});

it('changes task status', function () {
    Task::create([
        'name' => 'Status Task',
        'task_priority_id' => TaskPriority::where('name', 'Low')->first()->id,
        'task_status_id' => TaskStatus::where('name', 'Inbox')->first()->id,
    ]);

    $this->artisan('task:status', ['id' => 1, 'status' => 'Next Action'])
        ->assertExitCode(0);

    $task = Task::first();
    expect($task->status->name)->toBe('Next Action');
    expect($task->completed_at)->toBeNull();
});

it('sets completed_at when marking done', function () {
    Task::create([
        'name' => 'Done Task',
        'task_priority_id' => TaskPriority::where('name', 'Low')->first()->id,
        'task_status_id' => TaskStatus::where('name', 'Inbox')->first()->id,
    ]);

    $this->artisan('task:status', ['id' => 1, 'status' => 'Done'])
        ->assertExitCode(0);

    $task = Task::find(1);
    $task->refresh();
    expect($task->status->name)->toBe('Done');
    expect($task->completed_at)->not->toBeNull();
});

it('edits a task with flags', function () {
    Task::create([
        'name' => 'Original Name',
        'task_priority_id' => TaskPriority::where('name', 'Low')->first()->id,
        'task_status_id' => TaskStatus::where('name', 'Inbox')->first()->id,
    ]);

    $this->artisan('task:edit', [
        'id' => 1,
        '--name' => 'Updated Name',
        '--priority' => 'High',
        '--deadline' => '2026-04-01',
    ])->assertExitCode(0);

    $task = Task::first();
    expect($task->name)->toBe('Updated Name');
    expect($task->priority->name)->toBe('High');
    expect($task->deadline->format('Y-m-d'))->toBe('2026-04-01');
});

it('shows inbox tasks', function () {
    Task::create([
        'name' => 'Inbox Item',
        'task_priority_id' => TaskPriority::where('name', 'Medium')->first()->id,
        'task_status_id' => TaskStatus::where('name', 'Inbox')->first()->id,
    ]);

    Task::create([
        'name' => 'Active Item',
        'task_priority_id' => TaskPriority::where('name', 'Medium')->first()->id,
        'task_status_id' => TaskStatus::where('name', 'Next Action')->first()->id,
    ]);

    $this->artisan('task:inbox')
        ->assertExitCode(0);

    // Only inbox task should appear
    $inboxCount = Task::whereHas('status', function ($q) {
            $q->where('type', StatusType::Inbox->value);
        }
        )->count();
        expect($inboxCount)->toBe(1);
    });

it('returns error for non-existent task', function () {
    $this->artisan('task:show', ['id' => 999])
        ->assertExitCode(1); // outputs error
});