<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Song;
use App\Models\SongDailyStat;
use Carbon\Carbon;

class SongDailyStatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Chọn các bài hát đã có file audio thật và đang hiển thị
        $songs = Song::whereNotNull('file_path')->where('status', 'published')->get();

        if ($songs->isEmpty()) {
            $this->command->info('Không tìm thấy bài hát nào có audio file (file_path is not null) để seed thống kê.');
            return;
        }

        $this->command->info("Đang tạo dữ liệu lượt nghe ảo cho {$songs->count()} bài hát (30 ngày gần đây)...");

        // Xóa sạch bảng daily stats trước khi seed mới (để tránh lỗi duplicate key nếu chạy nhiều lần)
        DB::table('song_daily_stats')->truncate();

        $statsToInsert = [];

        foreach ($songs as $song) {
            $totalListens = 0;
            // Generate dữ liệu ngẫu nhiên trong 30 ngày qua
            for ($daysBack = 30; $daysBack >= 0; $daysBack--) {
                // Có 80% cơ hội ngày đó bài hát được nghe
                if (rand(1, 100) <= 80) {
                    // Random từ 10 đến 500 views mỗi ngày (tùy chỉnh cho ảo nhẹ)
                    // Nếu là top thì random cao hơn..
                    $isTopSong = ($song->id % 5 === 0); 
                    
                    $dailyPlayCount = $isTopSong ? rand(500, 3000) : rand(10, 200);
                    $statDate = Carbon::today()->subDays($daysBack)->toDateString();
                    
                    $totalListens += $dailyPlayCount;

                    $statsToInsert[] = [
                        'song_id'    => $song->id,
                        'stat_date'  => $statDate,
                        'play_count' => $dailyPlayCount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Đồng bộ hoá lại cột listens của bảng songs để logic hệ thống không bị lệch thống kê
            // (Không trigger event save)
            Song::withoutTimestamps(function () use ($song, $totalListens) {
                $song->update(['listens' => $totalListens]);
            });
        }

        // Chunk insert để database khỏi quá tải
        foreach (array_chunk($statsToInsert, 1000) as $chunk) {
            SongDailyStat::insert($chunk);
        }

        $this->command->info('Seed thành công dữ liệu Song Daily Stats!');
    }
}
