<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->index('created_at', 'comments_created_at_index');
            $table->index(['project_id', 'created_at'], 'comments_project_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_created_at_index');
            $table->dropIndex('comments_project_created_at_index');
        });
    }
};
