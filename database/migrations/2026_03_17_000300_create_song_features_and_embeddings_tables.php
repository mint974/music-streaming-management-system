<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('song_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained('songs')->cascadeOnDelete();

            $table->float('danceability')->nullable();
            $table->float('energy')->nullable();
            $table->float('valence')->nullable();
            $table->float('acousticness')->nullable();
            $table->float('instrumentalness')->nullable();
            $table->float('speechiness')->nullable();
            $table->float('liveness')->nullable();
            $table->float('tempo')->nullable();
            $table->float('loudness')->nullable();

            $table->string('feature_source', 50)->default('spotify_kaggle');
            $table->timestamps();

            $table->index('song_id');
            $table->index('feature_source');
            $table->unique(['song_id', 'feature_source']);
        });

        Schema::create('song_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained('songs')->cascadeOnDelete();

            $table->string('embedding_type', 50);
            $table->json('vector');
            $table->unsignedInteger('dimension');
            $table->string('model_version', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('song_id');
            $table->index('embedding_type');
            $table->index('model_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_embeddings');
        Schema::dropIfExists('song_features');
    }
};
