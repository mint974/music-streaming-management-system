<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('account_histories', 'lock_reason')) {
            Schema::table('account_histories', function (Blueprint $table) {
                $table->text('lock_reason')->nullable()->after('action');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('account_histories', 'lock_reason')) {
            Schema::table('account_histories', function (Blueprint $table) {
                $table->dropColumn('lock_reason');
            });
        }
    }
};
