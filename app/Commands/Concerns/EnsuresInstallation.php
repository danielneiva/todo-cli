<?php

namespace App\Commands\Concerns;

use App\Models\TaskStatus;
use Illuminate\Support\Facades\Schema;

trait EnsuresInstallation
{
    protected function checkInstallation(): bool
    {
        try {
            if (!Schema::hasTable('task_statuses') || TaskStatus::count() === 0) {
                $this->error('  Todo CLI is not set up yet. Run `todo-cli task:install` first.');
                return false;
            }
            return true;
        }
        catch (\Exception $e) {
            $this->error('  Todo CLI is not set up yet. Run `todo-cli task:install` first.');
            return false;
        }
    }
}