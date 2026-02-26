<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Add 'pending' status to subscriptions  ────────────────────────
        // MySQL: modify the ENUM column to include 'pending'
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('pending','active','expired','cancelled') DEFAULT 'active'");

        // ── 2. Create payments table  ─────────────────────────────────────────
        Schema::create('payments', function (Blueprint $table) {
            $table->id();                                   // ID_Payment (PK)
            $table->foreignId('subscription_id')
                  ->constrained('subscriptions')
                  ->cascadeOnDelete();                      // 1 subscription ↔ 1 payment
            $table->string('method')->default('VNPAY');     // Method (VNPAY, ...)
            $table->enum('status', [
                'pending',  // Đang chờ thanh toán
                'paid',     // Đã thanh toán thành công
                'failed',   // Thất bại / bị hủy
            ])->default('pending');
            $table->string('transaction_code')->nullable()->unique(); // TransactionCode (vnp_TxnRef)
            $table->timestamp('date')->nullable();          // Ngày thanh toán xong (Date)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('active','expired','cancelled') DEFAULT 'active'");
    }
};
