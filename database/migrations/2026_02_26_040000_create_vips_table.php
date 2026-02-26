<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vips', function (Blueprint $table) {
            // String slug primary key: monthly | quarterly | yearly | ...
            $table->string('id')->primary();
            $table->string('title');                          // Tên gói: "Premium Tháng", ...
            $table->text('description')->nullable();          // Mô tả quyền lợi
            $table->unsignedSmallInteger('duration_days');    // Số ngày hiệu lực
            $table->unsignedBigInteger('price');              // Giá (VNĐ, không dùng decimal)
            $table->boolean('is_active')->default(true);      // Ẩn/hiện gói
            $table->timestamps();
        });

        // Seed 3 gói mặc định
        DB::table('vips')->insert([
            [
                'id'           => 'monthly',
                'title'        => 'Premium Tháng',
                'description'  => 'Nghe nhạc không giới hạn, tải xuống offline, không quảng cáo. Gia hạn hàng tháng.',
                'duration_days'=> 30,
                'price'        => 49000,
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => 'quarterly',
                'title'        => 'Premium Quý',
                'description'  => 'Nghe nhạc không giới hạn, tải xuống offline, không quảng cáo. Tiết kiệm 15% so với gói tháng.',
                'duration_days'=> 90,
                'price'        => 125000,
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => 'yearly',
                'title'        => 'Premium Năm',
                'description'  => 'Nghe nhạc không giới hạn, tải xuống offline, không quảng cáo. Tiết kiệm 30% so với gói tháng.',
                'duration_days'=> 365,
                'price'        => 420000,
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vips');
    }
};
