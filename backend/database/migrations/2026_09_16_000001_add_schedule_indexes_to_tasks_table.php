<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['due_date', 'status'], 'tasks_due_date_status_index');
            $table->index(['status', 'due_date'], 'tasks_status_due_date_index');
            $table->index(['project_id', 'status'], 'tasks_project_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_due_date_status_index');
            $table->dropIndex('tasks_status_due_date_index');
            $table->dropIndex('tasks_project_id_status_index');
        });
    }
};
