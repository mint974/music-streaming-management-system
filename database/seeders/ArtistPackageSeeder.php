<?php

namespace Database\Seeders;

use App\Models\ArtistPackage;
use Illuminate\Database\Seeder;

class ArtistPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name'        => 'Gói Khởi đầu',
                'description' => 'Dành cho nghệ sĩ mới bắt đầu hành trình âm nhạc trên Blue Wave Music.',
                'price'       => 99000,
                'features'    => [
                    'Tải lên tối đa 10 bài hát / tháng',
                    'Tạo tối đa 2 album',
                    'Thống kê lượt nghe cơ bản',
                    'Huy hiệu Nghệ sĩ trên hồ sơ',
                ],
                'is_active'   => true,
            ],
            [
                'name'        => 'Gói Tiêu chuẩn',
                'description' => 'Phù hợp cho nghệ sĩ đang phát triển, muốn mở rộng tệp người nghe.',
                'price'       => 249000,
                'features'    => [
                    'Tải lên tối đa 30 bài hát / tháng',
                    'Tạo tối đa 5 album',
                    'Thống kê lượt nghe chi tiết',
                    'Huy hiệu Nghệ sĩ trên hồ sơ',
                    'Ưu tiên hiển thị trên trang Khám phá',
                ],
                'is_active'   => true,
            ],
            [
                'name'        => 'Gói Chuyên nghiệp',
                'description' => 'Dành cho nghệ sĩ chuyên nghiệp với đầy đủ công cụ quản lý và phân tích âm nhạc.',
                'price'       => 499000,
                'features'    => [
                    'Tải lên không giới hạn bài hát',
                    'Tạo không giới hạn album',
                    'Thống kê đầy đủ: lượt nghe, doanh thu, nhân khẩu học',
                    'Huy hiệu Nghệ sĩ xác minh ưu tiên',
                    'Ưu tiên hiển thị cao nhất trên trang Khám phá',
                    'Hỗ trợ kỹ thuật ưu tiên 24/7',
                ],
                'is_active'   => true,
            ],
        ];

        foreach ($packages as $data) {
            ArtistPackage::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
            $this->command->info("  ✓ {$data['name']}: " . number_format($data['price']) . ' đ/năm');
        }

        $this->command->info('✅ Đã tạo / cập nhật ' . count($packages) . ' gói đăng ký nghệ sĩ.');
    }
}
