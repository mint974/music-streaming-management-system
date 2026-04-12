<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listening_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('listening_histories', 'played_seconds')) {
                $table->unsignedInteger('played_seconds')->nullable()->after('source');
            }

            if (! Schema::hasColumn('listening_histories', 'played_percent')) {
                $table->decimal('played_percent', 5, 2)->nullable()->after('played_seconds');
            }

            if (! Schema::hasColumn('listening_histories', 'is_completed')) {
                $table->boolean('is_completed')->default(false)->after('played_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listening_histories', function (Blueprint $table) {
            if (Schema::hasColumn('listening_histories', 'is_completed')) {
                $table->dropColumn('is_completed');
            }

            if (Schema::hasColumn('listening_histories', 'played_percent')) {
                $table->dropColumn('played_percent');
            }

            if (Schema::hasColumn('listening_histories', 'played_seconds')) {
                $table->dropColumn('played_seconds');
            }
        });
    }
};
