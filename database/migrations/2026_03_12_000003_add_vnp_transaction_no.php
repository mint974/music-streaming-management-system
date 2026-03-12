<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lưu mã giao dịch nội bộ VNPAY (vnp_TransactionNo) — cần thiết cho Refund API
        Schema::table('payments', function (Blueprint $table) {
            $table->string('vnp_transaction_no')->nullable()->after('transaction_code');
            $table->string('vnp_pay_date', 14)->nullable()->after('vnp_transaction_no'); // yyyyMMddHHmmss
        });

        Schema::table('artist_registrations', function (Blueprint $table) {
            $table->string('vnp_transaction_no')->nullable()->after('transaction_code');
            $table->string('vnp_pay_date', 14)->nullable()->after('vnp_transaction_no'); // yyyyMMddHHmmss
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['vnp_transaction_no', 'vnp_pay_date']);
        });

        Schema::table('artist_registrations', function (Blueprint $table) {
            $table->dropColumn(['vnp_transaction_no', 'vnp_pay_date']);
        });
    }
};
