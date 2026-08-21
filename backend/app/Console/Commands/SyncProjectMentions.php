<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Project;
use App\ProjectMemberService;

#[Signature('project:sync-mentions')]
#[Description('Sync project members based on mentions/tags in project updates (comments) and tasks')]
class SyncProjectMentions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting project mentions synchronization...');

        // Fetch all projects with their comments and tasks
        $projects = Project::with(['comments', 'tasks'])->get();

        $this->info("Found {$projects->count()} projects to process.");

        $service = app(ProjectMemberService::class);
        $totalAdded = 0;

        foreach ($projects as $project) {
            $this->line("Processing project: <info>{$project->title}</info> (ID: {$project->id})");

            $initialMembersCount = $project->members()->count();

            // 1. Scan comments (project updates)
            foreach ($project->comments as $comment) {
                if ($comment->content) {
                    $service->addMentionedMembers($project, $comment->content);
                }
            }

            // 2. Scan tasks (titles, descriptions, and assignees)
            foreach ($project->tasks as $task) {
                // Title and assignee
                $explicitIds = array_filter([$task->assignee_id]);
                if ($task->title) {
                    $service->addMentionedMembers($project, $task->title, $explicitIds);
                } elseif ($explicitIds) {
                    $service->addMentionedMembers($project, null, $explicitIds);
                }

                // Description
                if ($task->description) {
                    $service->addMentionedMembers($project, $task->description);
                }
            }

            // Reload members to see how many were added
            $finalMembersCount = $project->members()->count();
            $addedCount = $finalMembersCount - $initialMembersCount;
            $totalAdded += $addedCount;

            if ($addedCount > 0) {
                $this->info("  -> Added {$addedCount} new member(s) to the project.");
            }
        }

        $this->info("Project mentions synchronization completed! Total new member associations: {$totalAdded}.");
    }
}
