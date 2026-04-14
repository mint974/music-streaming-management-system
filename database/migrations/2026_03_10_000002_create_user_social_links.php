<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 1NF: Replace JSON `users.social_links` column with a proper relational table.
 *
 * Before: users.social_links JSON {"facebook":"https://...","instagram":"https://..."}
 * After : user_social_links(id, user_id, platform, url) — each cell is atomic.
 *
 * BCNF: {user_id, platform} is a candidate key — every FD comes from it.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create user_social_links table ────────────────────────────────
        Schema::create('user_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_profile_id')
                ->constrained('artist_profiles')
                ->cascadeOnDelete();
            $table->string('platform', 30);   // facebook | instagram | youtube | tiktok | spotify | website
            $table->string('url', 500);

            // UNIQUE: one URL per platform per user
            $table->unique(['artist_profile_id', 'platform']);
            $table->index('artist_profile_id');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('user_social_links');
    }
};
