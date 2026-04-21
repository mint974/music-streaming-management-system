<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();

            // Nghệ sĩ sở hữu bài hát
            $table->foreignId('artist_profile_id')->constrained('artist_profiles')->cascadeOnDelete();

            // Thể loại (nullable — được chọn khi upload)
            $table->foreignId('genre_id')->constrained('genres')->cascadeOnDelete();

            // Album (1 bài hát thuộc 0 hoặc 1 album)
            $table->foreignId('album_id')->nullable()->constrained('albums')->nullOnDelete();

            // Thông tin cơ bản
            $table->string('title');                                    // Tên bài hát
            $table->unsignedInteger('duration')->default(0);            // Thời lượng (giây)

            // File audio
            $table->string('file_path')->nullable();                    // NULL = bài từ dataset (chỉ metadata)
            $table->string('file_mime', 50)->nullable();                // audio/mpeg, audio/flac …
            $table->unsignedBigInteger('file_size')->default(0);        // Bytes

            // Hình ảnh
            $table->string('cover_image')->nullable();                  // Ảnh bìa bài hát

            // Phát hành
            $table->date('released_date')->nullable();
            $table->timestamp('publish_at')->nullable();
            $table->boolean('is_vip')->default(false);                  // Chỉ cho Premium listener
            // Tags: tags + song_tags tables (1NF)

            // Vòng đời
            $table->enum('status', ['draft', 'pending', 'scheduled', 'published', 'hidden'])->default('draft');
            $table->unsignedBigInteger('listens')->default(0);
            $table->boolean('deleted')->default(false);

            $table->timestamps();

            $table->index(['artist_profile_id', 'status']);
            $table->index(['album_id']);
            $table->index('deleted');
            $table->index('listens');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
