<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change default value of milestones.is_completed to FALSE (0).
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE milestones MODIFY is_completed TINYINT(1) NOT NULL DEFAULT 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE milestones MODIFY is_completed TINYINT(1) NOT NULL DEFAULT 1');
    }
};
