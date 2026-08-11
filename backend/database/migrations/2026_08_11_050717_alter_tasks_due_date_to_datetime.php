<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE tasks MODIFY due_date DATETIME NULL');
        } catch (\Throwable $e) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->datetime('due_date')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE tasks MODIFY due_date DATE NULL');
        } catch (\Throwable $e) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->date('due_date')->nullable()->change();
            });
        }
    }
};
