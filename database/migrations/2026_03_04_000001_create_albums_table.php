<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->id();

            // Nghệ sĩ sở hữu album
            $table->foreignId('artist_profile_id')->constrained('artist_profiles')->cascadeOnDelete();

            $table->string('title');                                    // Tên album
            $table->text('description')->nullable();                    // Mô tả / release note
            $table->string('cover_image')->nullable();                  // Ảnh bìa album
            $table->date('released_date')->nullable();                  // Ngày phát hành

            $table->enum('status', ['draft', 'published'])->default('draft');

            $table->boolean('deleted')->default(false);
            $table->timestamps();

            $table->index(['artist_profile_id', 'status']);
            $table->index('deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
