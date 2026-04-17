<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultVipSeeder extends Seeder
{
    /**
     * Seed the application's VIP packages.
     */
    public function run(): void
    {
        $now = now();
        $vips = [
            [
                'id'           => 'monthly',
                'title'        => 'Premium Tháng',
                'description'  => 'Nghe nhạc không giới hạn, tải xuống offline, không quảng cáo. Gia hạn hàng tháng.',
                'duration_days'=> 30,
                'price'        => 49000,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id'           => 'quarterly',
                'title'        => 'Premium Quý',
                'description'  => 'Nghe nhạc không giới hạn, tải xuống offline, không quảng cáo. Tiết kiệm 15% so với gói tháng.',
                'duration_days'=> 90,
                'price'        => 125000,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id'           => 'yearly',
                'title'        => 'Premium Năm',
                'description'  => 'Nghe nhạc không giới hạn, tải xuống offline, không quảng cáo. Tiết kiệm 30% so với gói tháng.',
                'duration_days'=> 365,
                'price'        => 420000,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];

        foreach ($vips as $vip) {
            DB::table('vips')->updateOrInsert(
                ['id' => $vip['id']],
                $vip
            );
        }

        $this->command->info('VIP packages seeded successfully.');
    }
}
