<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change tasks.title from VARCHAR(255) to LONGTEXT
     * to support base64-encoded image attachments and rich HTML.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE tasks MODIFY title LONGTEXT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE tasks MODIFY title VARCHAR(255)');
    }
};
