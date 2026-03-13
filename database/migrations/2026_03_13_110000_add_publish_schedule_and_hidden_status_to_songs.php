<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            if (!Schema::hasColumn('songs', 'publish_at')) {
                $table->timestamp('publish_at')->nullable()->after('released_date')->index();
            }
        });

        DB::statement("ALTER TABLE songs MODIFY status ENUM('draft','pending','scheduled','published','hidden') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE songs MODIFY status ENUM('draft','pending','published') NOT NULL DEFAULT 'draft'");

        Schema::table('songs', function (Blueprint $table) {
            if (Schema::hasColumn('songs', 'publish_at')) {
                $table->dropColumn('publish_at');
            }
        });
    }
};
