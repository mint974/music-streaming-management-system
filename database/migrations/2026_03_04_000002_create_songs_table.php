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

            // Nghệ sĩ upload bài hát
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Thể loại (nullable — được chọn khi upload)
            $table->foreignId('genre_id')->nullable()->constrained('genres')->nullOnDelete();

            // Album (1 bài hát thuộc 0 hoặc 1 album)
            $table->foreignId('album_id')->nullable()->constrained('albums')->nullOnDelete();

            // Thông tin cơ bản
            $table->string('title');                                    // Tên bài hát
            $table->string('author')->nullable();                       // Tác giả / nhạc sĩ sáng tác
            $table->unsignedInteger('duration')->default(0);            // Thời lượng (giây)

            // File audio
            $table->string('file_path');                                // Đường dẫn file MP3/FLAC/WAV
            $table->string('file_mime', 50)->nullable();                // audio/mpeg, audio/flac …
            $table->unsignedBigInteger('file_size')->default(0);        // Bytes

            // Hình ảnh
            $table->string('cover_image')->nullable();                  // Ảnh bìa bài hát

            // Nội dung lời
            $table->longText('lyrics')->nullable();                     // Lời bài hát (plain hoặc LRC)
            $table->enum('lyrics_type', ['plain', 'lrc'])->default('plain');

            // Phát hành
            $table->date('released_date')->nullable();
            $table->boolean('is_vip')->default(false);                  // Chỉ cho Premium listener

            // Tags (tâm trạng, hoạt động, chủ đề) — JSON:
            // {"mood":["vui","lãng mạn"],"activity":["tập gym"],"topic":["tình yêu"]}
            $table->json('tags')->nullable();

            // Vòng đời
            $table->enum('status', ['draft', 'pending', 'published'])->default('draft');
            $table->unsignedBigInteger('listens')->default(0);
            $table->boolean('deleted')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'status']);
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
