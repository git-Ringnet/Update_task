<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->change();
        });

        // Set all existing tokens to never expire unless explicitly logged out
        DB::table('api_tokens')->update(['expires_at' => null]);
        DB::table('users')->whereNotNull('api_token')->update(['api_token_expires_at' => null]);
    }

    public function down(): void
    {
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable(false)->change();
        });
    }
};
