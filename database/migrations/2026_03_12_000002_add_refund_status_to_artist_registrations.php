<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artist_registrations', function (Blueprint $table) {
            // null = không cần hoàn / 'pending' = chờ xử lý / 'completed' = đã hoàn xong
            $table->enum('refund_status', ['pending', 'completed'])->nullable()->after('refunded_at');
            $table->unsignedBigInteger('refund_confirmed_by')->nullable()->after('refund_status');
            $table->timestamp('refund_confirmed_at')->nullable()->after('refund_confirmed_by');

            $table->foreign('refund_confirmed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('artist_registrations', function (Blueprint $table) {
            $table->dropForeign(['refund_confirmed_by']);
            $table->dropColumn(['refund_status', 'refund_confirmed_by', 'refund_confirmed_at']);
        });
    }
};
