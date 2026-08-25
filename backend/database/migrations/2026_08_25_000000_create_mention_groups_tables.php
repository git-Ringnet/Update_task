<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mention_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('mention_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mention_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['mention_group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mention_group_user');
        Schema::dropIfExists('mention_groups');
    }
};
