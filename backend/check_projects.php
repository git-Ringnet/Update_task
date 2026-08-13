<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$projects = App\Models\Project::withCount('milestones')->get();
foreach ($projects as $p) {
    echo "ID: {$p->id} | Title: {$p->title} | Milestones count: {$p->milestones_count}\n";
}
