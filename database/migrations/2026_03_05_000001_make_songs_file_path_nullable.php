<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cho phép file_path = NULL để các bài hát seed từ Kaggle dataset
 * (chỉ có metadata/lyrics, không có file âm thanh thực) có thể tồn tại
 * trong cùng bảng với các bài hát do artist upload.
 *
 * Phân biệt:
 *   file_path IS NULL     → bài từ Kaggle / chỉ có metadata
 *   file_path IS NOT NULL → bài do artist upload, có thể stream
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->string('file_path')->nullable(false)->change();
        });
    }
};
