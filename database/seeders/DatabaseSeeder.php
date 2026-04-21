<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Thứ tự quan trọng: mỗi seeder phụ thuộc vào dữ liệu từ seeder trước đó.
     *
     *  1. AdminSeeder            – Tài khoản quản trị viên hệ thống
     *  2. UserSeeder             – Người dùng mẫu (free / premium)
     *  3. MinhTanUserSeeder      – User dev cụ thể
    *  4. MinhTanActivitySeeder  – Lịch sử nghe + yêu thích cho tài khoản dev
     *  4. GenreSeeder            – Danh mục thể loại nhạc
     *  5. ArtistPackageSeeder    – Gói đăng ký nghệ sĩ
     *  6. ApprovedArtistSeeder   – Nghệ sĩ mẫu đã được duyệt + bài hát
     * 10. SongDailyStatSeeder    – Lượt nghe hằng ngày cơ bản cho tất cả bài hát
     * 11. ArtistStatsSeeder      – Dữ liệu thống kê chi tiết: follows, listening history,
     *                              song favorites (phụ thuộc vào ApprovedArtistSeeder)
     * 12. ReportTestDataSeeder   – 400 users + subscriptions + payments cho báo cáo admin
     *
     * KHÔNG chạy mặc định: ExpiryTestSeeder (chỉ dùng để test tính năng hết hạn gói)
     *   → php artisan db:seed --class=ExpiryTestSeeder
     */
    public function run(): void
    {
        $this->call([
            // ── Hệ thống cơ bản ──────────────────────────────────────────────
            DefaultRoleSeeder::class,
            DefaultVipSeeder::class,
            
            AdminSeeder::class,
            UserSeeder::class,
            MinhTanUserSeeder::class,

            // ── Danh mục & gói ───────────────────────────────────────────────
            GenreSeeder::class,
            ArtistPackageSeeder::class,

            // ── Nghệ sĩ & bài hát ────────────────────────────────────────────
            ApprovedArtistSeeder::class,
            CustomSongsSeeder::class,

            // ── Dữ liệu giả lập gợi ý (KNN) ──────────────────────────────────
            UserInteractionSeeder::class,

            // ── Các dữ liệu thống kê ngẫu nhiên ───
            SongDailyStatSeeder::class,      // Lượt nghe cơ bản (tất cả bài hát)
            ArtistStatsSeeder::class,        // Chi tiết thống kê nghệ sĩ mẫu
            ReportTestDataSeeder::class,     // Dữ liệu lớn cho báo cáo admin
            MinhTanActivitySeeder::class,    // Lịch sử nghe của Minh Tân

            // ── Dữ liệu test user dev ───────────────────────────────────────
            MinhTanSubscriptionSeeder::class,
            MinhTanArtistRegistrationSeeder::class,
        ]);
    }
}
