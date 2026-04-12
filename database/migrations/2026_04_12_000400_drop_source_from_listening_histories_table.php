<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('listening_histories', 'source')) {
            Schema::table('listening_histories', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('listening_histories', 'source')) {
            Schema::table('listening_histories', function (Blueprint $table) {
                $table->string('source', 30)->default('stream')->after('song_id');
            });
        }
    }
};