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
        Schema::table('songs', function (Blueprint $table) {
            $table->boolean('has_lyrics')->default(false)->after('lyrics_type');
            $table->unsignedBigInteger('default_lyric_id')->nullable()->after('has_lyrics');
            
            // Note: In some systems default_lyric_id is constrained, but doing constrained('song_lyrics')
            // can cause circular dependency issues during tear downs if not careful.
            $table->foreign('default_lyric_id')->references('id')->on('song_lyrics')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropForeign(['default_lyric_id']);
            $table->dropColumn(['has_lyrics', 'default_lyric_id']);
        });
    }
};
