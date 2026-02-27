<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('artist_packages');
            $table->string('artist_name');                   // Tên nghệ danh
            $table->text('bio')->nullable();                  // Giới thiệu bản thân

            // Trạng thái đơn đăng ký
            // pending_payment → pending_review → approved | rejected
            $table->string('status')->default('pending_payment');

            // Thông tin thanh toán
            $table->unsignedInteger('amount_paid')->default(0);
            $table->string('transaction_code')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();

            // Xét duyệt bởi admin
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_registrations');
    }
};
