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
        Schema::create('song_lyric_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_lyric_id')->constrained()->cascadeOnDelete();
            $table->integer('line_order');
            $table->integer('start_time_ms')->nullable();
            $table->integer('end_time_ms')->nullable();
            $table->text('content');
            $table->timestamps();

            $table->index(['song_lyric_id', 'line_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_lyric_lines');
    }
};
