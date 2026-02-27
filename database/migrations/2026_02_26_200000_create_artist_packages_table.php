<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Tên gói, VD: "Gói Nghệ sĩ Cơ bản"
            $table->string('description')->nullable();       // Mô tả ngắn
            $table->json('features')->nullable();            // Danh sách tính năng
            $table->unsignedInteger('price');                // Giá (VNĐ)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_packages');
    }
};
