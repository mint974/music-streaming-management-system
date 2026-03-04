<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tên nghệ danh (khác với name là tên thật)
            $table->string('artist_name', 100)->nullable()->after('avatar');

            // Tiểu sử nghệ sĩ
            $table->text('bio')->nullable()->after('artist_name');

            // Ảnh bìa kênh nghệ sĩ
            $table->string('cover_image')->nullable()->after('bio');

            // Liên kết mạng xã hội (JSON: {facebook, instagram, youtube, tiktok, spotify, website})
            $table->json('social_links')->nullable()->after('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['artist_name', 'bio', 'cover_image', 'social_links']);
        });
    }
};
