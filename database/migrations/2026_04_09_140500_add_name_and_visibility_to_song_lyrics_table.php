<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('song_lyrics', function (Blueprint $table) {
            $table->string('name', 100)->nullable()->after('song_id');
            $table->boolean('is_visible')->default(true)->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('song_lyrics', function (Blueprint $table) {
            $table->dropColumn(['name', 'is_visible']);
        });
    }
};
