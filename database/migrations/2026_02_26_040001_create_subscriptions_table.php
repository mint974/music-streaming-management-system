<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('vip_id');                        // FK → vips.id (string slug)
            $table->foreign('vip_id')
                  ->references('id')
                  ->on('vips')
                  ->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', [
                'pending',   // Chờ thanh toán
                'active',    // Đang hiệu lực
                'expired',   // Hết hạn
                'cancelled', // Đã hủy
            ])->default('active');
            $table->unsignedBigInteger('amount_paid');       // Số tiền thực thanh toán (VNĐ)
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')
                  ->constrained('subscriptions')
                  ->cascadeOnDelete();
            $table->string('method')->default('VNPAY');     // VNPAY, ...
            $table->enum('status', [
                'pending',  // Đang chờ
                'paid',     // Đã thanh toán
                'failed',   // Thất bại / bị hủy
            ])->default('pending');
            $table->string('transaction_code')->nullable()->unique(); // vnp_TxnRef
            $table->timestamp('date')->nullable();           // Ngày thanh toán xong
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
    }
};
