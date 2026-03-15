<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('artist_id')->constrained('users')->onDelete('cascade');
            $table->boolean('notify_in_app')->default(true);
            $table->boolean('notify_email')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'artist_id']);
            $table->index('artist_id');
        });

        Schema::create('saved_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('album_id')->constrained('albums')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'album_id']);
            $table->index('album_id');
        });

        Schema::create('listening_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('song_id')->constrained('songs')->onDelete('cascade');
            $table->string('source', 30)->default('stream');
            $table->timestamp('listened_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'listened_at']);
            $table->index('song_id');
        });

        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('notify_new_song')->default(true);
            $table->boolean('notify_new_album')->default(true);
            $table->boolean('notify_in_app')->default(true);
            $table->boolean('notify_email')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
        Schema::dropIfExists('listening_histories');
        Schema::dropIfExists('saved_albums');
        Schema::dropIfExists('artist_follows');
    }
};
