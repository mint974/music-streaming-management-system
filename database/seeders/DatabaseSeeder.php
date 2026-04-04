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
     *  4. GenreSeeder            – Danh mục thể loại nhạc
     *  5. ArtistPackageSeeder    – Gói đăng ký nghệ sĩ
     *  6. ApprovedArtistSeeder   – Nghệ sĩ mẫu đã được duyệt + bài hát
     *  7. SpotifyDatasetSeeder   – Bài hát từ Spotify dataset (spotify_seed_data.json)
     *  8. CustomSongsSeeder      – Bài hát custom kèm file media
     *  9. UpdateSongLyricsSeeder – Lyrics (bỏ qua nếu thiếu file CSV)
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
            AdminSeeder::class,
            UserSeeder::class,
            MinhTanUserSeeder::class,

            // ── Danh mục & gói ───────────────────────────────────────────────
            GenreSeeder::class,
            ArtistPackageSeeder::class,

            // ── Nghệ sĩ & bài hát ────────────────────────────────────────────
            ApprovedArtistSeeder::class,
            SpotifyDatasetSeeder::class,
            CustomSongsSeeder::class,
            UpdateSongLyricsSeeder::class,   // Tự bỏ qua nếu thiếu CSV

            // ── Dữ liệu thống kê ─────────────────────────────────────────────
            SongDailyStatSeeder::class,      // Lượt nghe cơ bản (tất cả bài hát)
            ArtistStatsSeeder::class,        // Chi tiết thống kê nghệ sĩ mẫu
            ReportTestDataSeeder::class,     // Dữ liệu lớn cho báo cáo admin
        ]);
    }
}
