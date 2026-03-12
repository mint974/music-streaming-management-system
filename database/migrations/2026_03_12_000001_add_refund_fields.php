<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hoàn tiền cho giao dịch đăng ký VIP (subscription cancel > 1/2 thời gian)
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('refund_amount')->nullable()->after('date');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
        });

        // Hoàn tiền cho đơn đăng ký nghệ sĩ bị từ chối
        Schema::table('artist_registrations', function (Blueprint $table) {
            $table->unsignedBigInteger('refund_amount')->nullable()->after('paid_at');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['refund_amount', 'refunded_at']);
        });

        Schema::table('artist_registrations', function (Blueprint $table) {
            $table->dropColumn(['refund_amount', 'refunded_at']);
        });
    }
};
