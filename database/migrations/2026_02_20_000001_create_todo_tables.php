<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // inbox, active, done, cancelled
        });

        Schema::create('task_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('level')->default(0); // higher = more urgent
        });

        Schema::create('task_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('task_category_id')->nullable()->constrained('task_categories')->nullOnDelete();
            $table->foreignId('task_priority_id')->constrained('task_priorities');
            $table->foreignId('task_status_id')->constrained('task_statuses');
            $table->date('deadline')->nullable();
            $table->date('expected_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_categories');
        Schema::dropIfExists('task_priorities');
        Schema::dropIfExists('task_statuses');
    }
};