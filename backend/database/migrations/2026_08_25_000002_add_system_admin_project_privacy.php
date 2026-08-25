<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_system_admin')->default(false)->index()->after('is_admin');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('hidden_from_admin')->default(false)->index()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('hidden_from_admin');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_system_admin');
        });
    }
};
