<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Song;
use App\Models\ArtistProfile;
use App\Models\SongDailyStat;
use App\Models\ListeningHistory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class StandardizedReportSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('==== CHUẨN HÓA DỮ LIỆU BÁO CÁO (BALANCED DATA) ====');

        // 1. Dọn dẹp dữ liệu thống kê cũ để làm mới
        $this->command->info('>> Đang dọn dẹp dữ liệu cũ...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('song_daily_stats')->truncate();
        DB::table('listening_histories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Lấy danh sách bài hát và phân nhóm theo nghệ sĩ
        $songsByArtist = Song::where('status', 'published')
            ->where('deleted', false)
            ->get()
            ->groupBy('artist_profile_id');

        if ($songsByArtist->isEmpty()) {
            $this->command->error('Không tìm thấy bài hát nào để seed!');
            return;
        }

        $this->command->info('>> Tìm thấy ' . $songsByArtist->count() . ' nghệ sĩ có bài hát.');

        // 3. Lấy danh sách Report Users (đã tạo từ ReportTestDataSeeder)
        $reportUserIds = User::where('email', 'like', 'report_user_%@test.com')
            ->pluck('id')
            ->toArray();

        if (empty($reportUserIds)) {
            $this->command->warn('Không thấy Report Users. Sẽ dùng tất cả users hiện có.');
            $reportUserIds = User::pluck('id')->toArray();
        }

        $daysToSeed = 90;
        $statsToInsert = [];
        $historiesToInsert = [];

        // 4. Seed dữ liệu cho từng nghệ sĩ một cách cân bằng
        foreach ($songsByArtist as $artistId => $songs) {
            $artist = ArtistProfile::find($artistId);
            $stageName = $artist->stage_name ?? 'Unknown';
            
            // Phân loại "độ hot" ngẫu nhiên để biểu đồ trông tự nhiên nhưng không lệch quá mức
            // 1: Top (15%), 2: Mid (50%), 3: Emerging (35%)
            $tierRand = rand(1, 100);
            if ($tierRand <= 15) {
                $baseDailyListens = rand(200, 500); // Top
            } elseif ($tierRand <= 65) {
                $baseDailyListens = rand(50, 150);  // Mid
            } else {
                $baseDailyListens = rand(5, 40);    // Emerging
            }

            $this->command->info("   - Seeding cho {$stageName} (Tier " . ($tierRand <= 15 ? 'Top' : ($tierRand <= 65 ? 'Mid' : 'Emerging')) . ")...");

            foreach ($songs as $song) {
                $totalSongListens = 0;

                for ($d = $daysToSeed; $d >= 0; $d--) {
                    $date = Carbon::today()->subDays($d);
                    
                    // Biến thiên ngẫu nhiên theo ngày (cuối tuần nghe nhiều hơn)
                    $dayVariation = rand(80, 130) / 100;
                    $weekendBoost = $date->isWeekend() ? 1.4 : 1.0;
                    
                    // Lượt nghe hằng ngày của bài hát này
                    $dailyPlay = intval(($baseDailyListens / $songs->count()) * $dayVariation * $weekendBoost);
                    if ($dailyPlay < 1 && rand(1, 5) == 1) $dailyPlay = 1;

                    if ($dailyPlay > 0) {
                        $statsToInsert[] = [
                            'song_id' => $song->id,
                            'stat_date' => $date->toDateString(),
                            'play_count' => $dailyPlay,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ];
                        
                        $totalSongListens += $dailyPlay;

                        // Tạo listening history thực tế cho bài hát
                        // Quy mô: 5-10% lượt nghe hằng ngày sẽ có record trong history để phân tích sâu
                        $historyCount = max(1, intval($dailyPlay * (rand(5, 15) / 100)));
                        
                        for ($i = 0; $i < $historyCount; $i++) {
                            $playedSec = rand(5, $song->duration ?: 210);
                            $isCompleted = ($playedSec > ($song->duration * 0.9)) ? 1 : 0;
                            // Giả lập skip: nếu nghe dưới 30s
                            $isSkipped = ($playedSec < 30) ? 1 : 0;

                            $historiesToInsert[] = [
                                'user_id' => $reportUserIds[array_rand($reportUserIds)],
                                'song_id' => $song->id,
                                'listened_at' => (clone $date)->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                                'played_seconds' => $playedSec,
                                'played_percent' => $song->duration ? round(($playedSec / $song->duration) * 100, 2) : 50,
                                'is_completed' => $isCompleted,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ];
                        }
                    }

                    if (count($statsToInsert) >= 1000) {
                        SongDailyStat::insert($statsToInsert);
                        $statsToInsert = [];
                    }
                    if (count($historiesToInsert) >= 500) {
                        ListeningHistory::insert($historiesToInsert);
                        $historiesToInsert = [];
                    }
                }

                $song->update(['listens' => $totalSongListens]);
            }
        }

        // Insert nốt dữ liệu còn lại
        if (!empty($statsToInsert)) SongDailyStat::insert($statsToInsert);
        if (!empty($historiesToInsert)) ListeningHistory::insert($historiesToInsert);

        $this->command->info('==== CHUẨN HÓA DỮ LIỆU HOÀN TẤT ====');
    }
}
