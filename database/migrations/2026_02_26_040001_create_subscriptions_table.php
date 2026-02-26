<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();                                    // ID_Subscription (bigint auto-increment)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();                       // 1 user có nhiều subscriptions
            $table->string('vip_id');                        // FK → vips.id (string)
            $table->foreign('vip_id')
                  ->references('id')
                  ->on('vips')
                  ->restrictOnDelete();                      // 1 gói vip có nhiều subscriptions
            $table->date('start_date');                      // Ngày bắt đầu
            $table->date('end_date');                        // Ngày kết thúc
            $table->enum('status', [
                'active',    // Đang hiệu lực
                'expired',   // Hết hạn
                'cancelled', // Đã hủy
            ])->default('active');
            $table->unsignedBigInteger('amount_paid');       // Số tiền thực thanh toán (VNĐ)
            $table->timestamps();                            // created_at = ngày đăng ký
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
