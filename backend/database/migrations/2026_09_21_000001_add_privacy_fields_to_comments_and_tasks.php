<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comments')) {
            Schema::table('comments', function (Blueprint $table) {
                if (!Schema::hasColumn('comments', 'is_private')) {
                    $table->boolean('is_private')->default(false)->after('type');
                }
                if (!Schema::hasColumn('comments', 'private_user_ids')) {
                    $table->json('private_user_ids')->nullable()->after('is_private');
                }
            });
        }

        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('tasks', 'is_private')) {
                    $table->boolean('is_private')->default(false)->after('status');
                }
                if (!Schema::hasColumn('tasks', 'private_user_ids')) {
                    $table->json('private_user_ids')->nullable()->after('is_private');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('comments')) {
            Schema::table('comments', function (Blueprint $table) {
                if (Schema::hasColumn('comments', 'private_user_ids')) {
                    $table->dropColumn('private_user_ids');
                }
                if (Schema::hasColumn('comments', 'is_private')) {
                    $table->dropColumn('is_private');
                }
            });
        }

        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (Schema::hasColumn('tasks', 'private_user_ids')) {
                    $table->dropColumn('private_user_ids');
                }
                if (Schema::hasColumn('tasks', 'is_private')) {
                    $table->dropColumn('is_private');
                }
            });
        }
    }
};
