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
            $table->string('name');                              // Tên gói
            $table->text('description')->nullable();             // Mô tả
            // features: artist_package_features table (1NF)
            $table->unsignedInteger('price');                    // Giá (VNĐ)
            $table->unsignedSmallInteger('duration_days')->default(365); // Hiệu lực (ngày)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_packages');
    }
};
