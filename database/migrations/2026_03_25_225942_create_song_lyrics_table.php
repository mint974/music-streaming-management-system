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
        Schema::create('song_lyrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100)->nullable();
            $table->string('language_code', 10)->default('vi');
            $table->enum('type', ['plain', 'synced'])->default('plain');
            $table->enum('source', ['artist', 'ai', 'admin', 'import'])->default('artist');
            $table->enum('status', ['draft', 'verified', 'published', 'rejected'])->default('draft');
            $table->longText('raw_text')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_lyrics');
    }
};
