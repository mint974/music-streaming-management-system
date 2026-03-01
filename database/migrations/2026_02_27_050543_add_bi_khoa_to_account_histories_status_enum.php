<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL requires redefining the full ENUM to add a value
        DB::statement("
            ALTER TABLE account_histories
            MODIFY COLUMN status
            ENUM('Đang hoạt động','Bị khóa','Bị vô hiệu hóa','Đang yêu cầu khôi phục')
            NOT NULL DEFAULT 'Đang hoạt động'
        ");
    }

    public function down(): void
    {
        // Migrate existing 'Bị khóa' rows to a valid value before removing it from enum
        DB::table('account_histories')
            ->where('status', 'Bị khóa')
            ->update(['status' => 'Đang hoạt động']);

        DB::statement("
            ALTER TABLE account_histories
            MODIFY COLUMN status
            ENUM('Đang hoạt động','Bị vô hiệu hóa','Đang yêu cầu khôi phục')
            NOT NULL DEFAULT 'Đang hoạt động'
        ");
    }
};
