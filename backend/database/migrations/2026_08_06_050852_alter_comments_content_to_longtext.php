<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change comments.content from TEXT (64KB) to LONGTEXT (4GB)
     * to support base64-encoded image attachments.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE comments MODIFY content LONGTEXT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE comments MODIFY content TEXT');
    }
};
