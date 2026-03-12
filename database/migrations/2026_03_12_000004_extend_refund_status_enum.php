<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mở rộng enum: thêm 'processing' (VNPAY chấp nhận) và 'failed' (thất bại)
        DB::statement("ALTER TABLE artist_registrations MODIFY refund_status ENUM('pending','processing','completed','failed') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE artist_registrations MODIFY refund_status ENUM('pending','completed') NULL");
    }
};
