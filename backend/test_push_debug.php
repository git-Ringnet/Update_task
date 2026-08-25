<?php

use App\Models\Project;
use App\Models\Comment;
use App\Models\PushSubscription;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    Illuminate\Http\Request::capture()
);

// Get the latest comment from an old project (or any project)
$comment = Comment::orderBy('id', 'desc')->first();

if (!$comment) {
    echo "No comments found in the database.\n";
    exit;
}

echo "=== DIAGNOSING COMMENT ID: {$comment->id} ===\n";
echo "Project ID: {$comment->project_id}\n";
echo "User ID (author): {$comment->user_id}\n";
echo "Content: {$comment->content}\n";

$project = $comment->project()->with('members')->first();
if (!$project) {
    echo "[-] Error: Project not found for this comment!\n";
    exit;
}

echo "\n=== PROJECT DETAILS ===\n";
echo "Title: {$project->title}\n";
echo "Created By: " . ($project->created_by ?? 'NULL') . "\n";
echo "Lead ID: " . ($project->lead_id ?? 'NULL') . "\n";

echo "\n=== MEMBERS ===\n";
foreach ($project->members as $member) {
    echo "Member: ID {$member->id} ({$member->name}) - is_admin: " . ($member->is_admin ? 'Yes' : 'No') . "\n";
}

$adminIds = User::where('is_admin', true)->pluck('id');
echo "\n=== ADMINS IN DB ===\n";
foreach ($adminIds as $adminId) {
    $u = User::find($adminId);
    echo "Admin: ID {$adminId} ({$u->name})\n";
}

$recipientIds = $project->members->pluck('id')
    ->push($project->created_by)
    ->push($project->lead_id)
    ->concat($adminIds)
    ->filter()
    ->unique()
    ->reject(fn ($id) => (int) $id === (int) $comment->user_id)
    ->values();

echo "\n=== FINAL RECIPIENT IDS (Excluding author ID {$comment->user_id}) ===\n";
print_r($recipientIds->toArray());

if ($recipientIds->isEmpty()) {
    echo "[-] Warning: Recipient list is empty!\n";
}

$subscriptions = PushSubscription::whereIn('user_id', $recipientIds)->get();
echo "\n=== PUSH SUBSCRIPTIONS FOUND ===\n";
if ($subscriptions->isEmpty()) {
    echo "[-] Warning: No push subscriptions found for these recipient IDs in the push_subscriptions table!\n";
} else {
    foreach ($subscriptions as $sub) {
        echo "Sub ID: {$sub->id} for User ID: {$sub->user_id} - Endpoint: " . substr($sub->endpoint, 0, 30) . "...\n";
    }
}

echo "\n=== VAPID CONFIG ===\n";
echo "Subject: " . (config('webpush.subject') ? 'Configured' : 'MISSING') . "\n";
echo "Public Key: " . (config('webpush.public_key') ? 'Configured' : 'MISSING') . "\n";
echo "Private Key: " . (config('webpush.private_key') ? 'Configured' : 'MISSING') . "\n";
