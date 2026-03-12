<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Đưa các giá trị trung gian về 'pending' trước khi thu hẹp enum
        DB::statement("UPDATE artist_registrations SET refund_status = 'pending' WHERE refund_status IN ('processing', 'failed')");

        DB::statement("ALTER TABLE artist_registrations MODIFY refund_status ENUM('pending','completed') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE artist_registrations MODIFY refund_status ENUM('pending','processing','completed','failed') NULL");
    }
};
