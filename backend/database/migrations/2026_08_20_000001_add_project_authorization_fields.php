<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->index();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('lead_id')
                ->constrained('users')->nullOnDelete();
        });

        DB::table('projects')->orderBy('id')->chunkById(100, function ($projects) {
            foreach ($projects as $project) {
                $creatorId = DB::table('comments')
                    ->where('project_id', $project->id)
                    ->where('content', 'like', 'Đã tạo dự án mới%')
                    ->orderBy('id')
                    ->value('user_id');

                $creatorId ??= DB::table('comments')
                    ->where('project_id', $project->id)
                    ->orderBy('id')
                    ->value('user_id');

                DB::table('projects')->where('id', $project->id)->update([
                    'created_by' => $creatorId ?: $project->lead_id,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
