<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ghim dự án được lưu theo từng user trong pinned_projects.
     * Cột projects.is_pinned (global) không còn được dùng.
     */
    public function up(): void
    {
        DB::table('projects')->update(['is_pinned' => false]);
    }

    public function down(): void
    {
        // Không khôi phục trạng thái ghim global cũ.
    }
};
