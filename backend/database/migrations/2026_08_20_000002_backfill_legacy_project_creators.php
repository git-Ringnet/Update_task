<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('projects')->whereNull('created_by')->orderBy('id')->chunkById(100, function ($projects) {
            foreach ($projects as $project) {
                $creatorId = DB::table('comments')
                    ->where('project_id', $project->id)
                    ->orderBy('id')
                    ->value('user_id');

                if ($creatorId) {
                    DB::table('projects')->where('id', $project->id)->update(['created_by' => $creatorId]);
                }
            }
        });
    }

    public function down(): void
    {
        // Historical creator ownership is intentionally retained on rollback.
    }
};
