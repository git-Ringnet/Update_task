<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add avatar column to users if not present
        if (!Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('email');
            });
        }

        // Customers Table (Khách hàng / Mối quan hệ)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Trung Nguyên Coffee, MB Bank Đà Nẵng
            $table->string('code')->nullable();
            $table->enum('type', ['customer', 'vendor', 'internal', 'other'])->default('customer');
            $table->enum('status', ['green', 'yellow', 'red'])->default('green');
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamps();
        });

        // Projects Table (Dự án)
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('title'); // e.g. Dự án tổng đài Grandstream Cafe Trung Nguyên
            $table->foreignId('lead_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('health', ['green', 'yellow', 'red'])->default('green');
            $table->enum('tracking_status', ['following', 'not_following', 'completed'])->default('following');
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamps();
        });

        // Tasks Table (Công việc)
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'review', 'done'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        // Task Comments & Discussion Table (Thay thế chat nhóm Zalo)
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained('tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->string('type')->default('comment'); // comment, health_update, status_change
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('customers');
    }
};
