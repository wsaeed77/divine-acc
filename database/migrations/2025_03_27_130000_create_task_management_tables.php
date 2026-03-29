<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lkp_task_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->string('naming_pattern', 255);
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->enum('recurrence', [
                'annual',
                'quarterly',
                'monthly',
                'per_paye_frequency',
                'one_off',
            ]);
            $table->string('deadline_source', 255)->nullable();
            $table->boolean('deadline_manual')->default(false);
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('breakdown_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('description', 500)->nullable();
            $table->foreignId('task_type_id')->nullable()->constrained('lkp_task_types')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('breakdown_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('breakdown_templates')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('description', 500);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_type_id')->constrained('lkp_task_types')->restrictOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('task_name', 255);
            $table->enum('status', ['active', 'completed', 'switched_off', 'deleted'])->default('active');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('monitor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('notify_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('latest_action_id')->nullable()->constrained('lkp_action_statuses')->nullOnDelete();
            $table->date('latest_action_date')->nullable();
            $table->date('target_date')->nullable();
            $table->boolean('target_date_manual')->default(false);
            $table->date('deadline_date')->nullable();
            $table->date('period_date')->nullable();
            $table->decimal('time_estimate', 5, 2)->nullable();
            $table->text('progress_notes')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('breakdown_template_id')->nullable()->constrained('breakdown_templates')->nullOnDelete();
            $table->boolean('is_favourite')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['client_id', 'task_type_id', 'status']);
        });

        Schema::create('task_breakdown_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('description', 500);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('task_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('field_changed', 50);
            $table->string('old_value', 255)->nullable();
            $table->string('new_value', 255)->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_history');
        Schema::dropIfExists('task_breakdown_items');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('breakdown_template_items');
        Schema::dropIfExists('breakdown_templates');
        Schema::dropIfExists('lkp_task_types');
    }
};
