<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name');                              // Tên thể loại
            $table->string('slug')->unique();                    // URL-friendly slug
            $table->text('description')->nullable();             // Mô tả ngắn
            $table->string('icon')->nullable();                  // Font Awesome class (e.g. "fa-solid fa-guitar")
            $table->string('color', 20)->default('#6366f1');     // Màu nền icon (hex)
            $table->string('cover_image')->nullable();           // Đường dẫn ảnh bìa
            $table->unsignedSmallInteger('sort_order')->default(0); // Thứ tự hiển thị
            $table->boolean('is_active')->default(true);         // Ẩn/hiện
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genres');
    }
};
