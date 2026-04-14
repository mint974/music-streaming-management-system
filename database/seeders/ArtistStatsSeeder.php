<?php

namespace Database\Seeders;

use App\Models\ArtistFollow;
use App\Models\Song;
use App\Models\SongDailyStat;
use App\Models\SongFavorite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ArtistStatsSeeder
 * ─────────────────
 * Tạo dữ liệu test cho toàn bộ chức năng thống kê nghệ sĩ:
 *   • song_daily_stats     → biểu đồ lượt nghe 30 ngày
 *   • listening_histories  → phân bố thính giả (giới tính/độ tuổi/nguồn phát/giờ)
 *   • artist_follows       → tăng trưởng followers
 *   • song_favorites       → yêu thích
 *
 * Sử dụng: php artisan db:seed --class=ArtistStatsSeeder
 */
class ArtistStatsSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Tìm artist ─────────────────────────────────────────────────────
        $artist = User::where('email', 'artist.seed@bluewavemusic.com')->first();
        if (!$artist) {
            $this->command->error('Không tìm thấy artist.seed@bluewavemusic.com. Hãy chạy ApprovedArtistSeeder trước.');
            return;
        }
        $artistId = $artist->id;
        $artistProfileId = (int) ($artist->artistProfile?->id ?? 0);

        if ($artistProfileId <= 0) {
            $this->command->error('Artist chưa có artist_profile. Hãy chạy seeder hồ sơ nghệ sĩ trước.');
            return;
        }

        $this->command->info("Artist: {$artist->name} (ID={$artistId})");

        // ── 2. Bài hát của artist ─────────────────────────────────────────────
        $songs = Song::where('artist_profile_id', $artistProfileId)->where('deleted', false)->get();
        if ($songs->isEmpty()) {
            $this->command->error('Nghệ sĩ chưa có bài hát. Hãy chạy ApprovedArtistSeeder trước.');
            return;
        }
        $songIds = $songs->pluck('id')->toArray();
        $this->command->info("Tìm thấy {$songs->count()} bài hát.");

        // ── 3. Đảm bảo đủ listeners ──────────────────────────────────────────
        $listeners = User::whereHas('roles', fn ($query) => $query->whereIn('slug', ['free', 'premium']))
            ->where('deleted', false)
            ->get();

        if ($listeners->count() < 30) {
            $need = 30 - $listeners->count();
            $this->command->info("Tạo thêm {$need} fake listeners...");
            $this->createFakeListeners($need);
            $listeners = User::whereHas('roles', fn ($query) => $query->whereIn('slug', ['free', 'premium']))
                ->where('deleted', false)
                ->get();
        }
        $listenerIds = $listeners->pluck('id')->toArray();
        $this->command->info("Có {$listeners->count()} listeners.");

        // ── 4. Xóa dữ liệu cũ ────────────────────────────────────────────────
        $this->command->info('Xóa dữ liệu cũ...');
        DB::table('song_daily_stats')->whereIn('song_id', $songIds)->delete();
        DB::table('listening_histories')->whereIn('song_id', $songIds)->delete();
        DB::table('song_favorites')->whereIn('song_id', $songIds)->delete();
        DB::table('artist_follows')->where('followed_artist_profile_id', $artistProfileId)->delete();

        // ── 5. Daily stats ────────────────────────────────────────────────────
        $this->command->info('Seeding song_daily_stats...');
        $this->seedDailyStats($songs, $songIds);

        // ── 6. Listening histories ────────────────────────────────────────────
        $this->command->info('Seeding listening_histories...');
        $this->seedListeningHistories($songs, $listeners);

        // ── 7. Follows ────────────────────────────────────────────────────────
        $this->command->info('Seeding artist_follows...');
        $this->seedFollows($artistProfileId, $listenerIds);

        // ── 8. Favorites ──────────────────────────────────────────────────────
        $this->command->info('Seeding song_favorites...');
        $this->seedFavorites($songIds, $listenerIds);

        // ── 9. Cập nhật cột listens ───────────────────────────────────────────
        $this->command->info('Cập nhật listens counts...');
        $this->syncListensCounts($songIds);

        // ── 10. Summary ───────────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('✅ Seeder hoàn thành!');
        $this->command->table(
            ['Bảng', 'Số dòng đã thêm'],
            [
                ['song_daily_stats',    DB::table('song_daily_stats')->whereIn('song_id', $songIds)->count()],
                ['listening_histories', DB::table('listening_histories')->whereIn('song_id', $songIds)->count()],
                ['artist_follows',      DB::table('artist_follows')->where('followed_artist_profile_id', $artistProfileId)->count()],
                ['song_favorites',      DB::table('song_favorites')->whereIn('song_id', $songIds)->count()],
            ]
        );
    }

    // ── Fake Listeners ────────────────────────────────────────────────────────

    private function createFakeListeners(int $count): void
    {
        $genders    = ['Nam', 'Nam', 'Nam', 'Nữ', 'Nữ', 'Khác'];
        $birthYears = range(1983, 2007);
        $hashed     = Hash::make('password');

        for ($i = 0; $i < $count; $i++) {
            $yr = $birthYears[array_rand($birthYears)];
            $roleSlug = ($i % 4 === 0) ? 'premium' : 'free';

            $listener = User::create([
                'name'              => 'Fan ' . ($i + 1) . ' of Huy Hoang',
                'email'             => 'fan_hh_' . $i . '_' . time() . '@statstest.local',
                'password'          => $hashed,
                'gender'            => $genders[$i % count($genders)],
                'birthday'          => Carbon::create($yr, ($i % 12) + 1, ($i % 28) + 1)->format('Y-m-d'),
                'deleted'           => false,
                'email_verified_at' => now(),
                'created_at'        => now()->subDays(rand(30, 180)),
                'updated_at'        => now(),
            ]);

            $listener->syncRoles([$roleSlug]);
        }
    }

    // ── song_daily_stats: 30 ngày với trend & weekend spike ──────────────────

    private function seedDailyStats($songs, array $songIds): void
    {
        $now    = Carbon::now();
        $rows   = [];
        $count  = $songs->count();

        foreach ($songs as $rank => $song) {
            // Bài xếp hạng cao hơn = lượt nghe cao hơn
            $baseMin = (int)(50000 / ($rank + 1));
            $baseMax = (int)(200000 / ($rank + 1));

            for ($d = 29; $d >= 0; $d--) {
                $date      = $now->copy()->subDays($d)->toDateString();
                $dow       = (int) Carbon::parse($date)->dayOfWeek; // 0=Sun,6=Sat
                $isWeekend = in_array($dow, [0, 6]);

                // Trend: lượt nghe tăng dần về hiện tại
                $trendFactor  = 1.0 + (29 - $d) / 29 * 0.4;
                $weekendBonus = $isWeekend ? (rand(120, 160) / 100) : 1.0;
                $noise        = rand(75, 125) / 100;

                $play = (int)(rand($baseMin, $baseMax) * $trendFactor * $weekendBonus * $noise);
                if ($play <= 0) continue;

                $rows[] = [
                    'song_id'    => $song->id,
                    'stat_date'  => $date,
                    'play_count' => $play,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('song_daily_stats')->insertOrIgnore($chunk);
        }
    }

    // ── listening_histories: đa dạng giờ nghe, ngày ────────────────────────

    private function seedListeningHistories($songs, $listeners): void
    {
        $hours    = $this->buildHourWeights();
        $now      = Carbon::now();
        $songArr  = $songs->all(); // plain array để index trực tiếp
        $rows     = [];

        foreach ($listeners as $user) {
            $plays = rand(8, 60);
            for ($j = 0; $j < $plays; $j++) {
                $song    = $songArr[array_rand($songArr)];
                $daysAgo = rand(0, 29);
                $h       = $this->weightedRandom($hours);
                $ts      = $now->copy()->subDays($daysAgo)->setHour($h)->setMinute(rand(0,59))->setSecond(rand(0,59));
                $tsStr   = $ts->format('Y-m-d H:i:s');

                $rows[] = [
                    'user_id'     => $user->id,
                    'song_id'     => $song->id,
                    'listened_at' => $tsStr,
                    'created_at'  => $tsStr,
                    'updated_at'  => $tsStr,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('listening_histories')->insert($chunk);
        }
    }

    // ── artist_follows: spike tuần gần nhất ──────────────────────────────────

    private function seedFollows(int $artistProfileId, array $listenerIds): void
    {
        $now    = Carbon::now();
        $rows   = [];
        $seen   = [];
        $total  = min(count($listenerIds), rand(40, 80));

        $shuffled = $listenerIds;
        shuffle($shuffled);
        $selected = array_slice($shuffled, 0, $total);

        foreach ($selected as $i => $userId) {
            if (isset($seen[$userId])) continue;
            $seen[$userId] = true;

            // 55% follows trong 7 ngày gần nhất → xu hướng tăng rõ
            $daysAgo   = ($i < (int)($total * 0.55)) ? rand(0, 6) : rand(7, 60);
            $createdAt = $now->copy()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0,59));

            $rows[] = [
                'user_id'       => $userId,
                'followed_artist_profile_id' => $artistProfileId,
                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('artist_follows')->insertOrIgnore($chunk);
        }
    }

    // ── song_favorites ────────────────────────────────────────────────────────

    private function seedFavorites(array $songIds, array $listenerIds): void
    {
        $now  = Carbon::now();
        $rows = [];
        $seen = [];

        foreach ($listenerIds as $userId) {
            // Mỗi user yêu thích 1-4 bài
            $picks = array_slice(
                array_unique(array_map(fn($_) => $songIds[array_rand($songIds)], range(1, rand(1,4)))),
                0, 4
            );
            foreach ($picks as $songId) {
                $key = "{$userId}_{$songId}";
                if (isset($seen[$key])) continue;
                $seen[$key] = true;
                $ts = $now->copy()->subDays(rand(0, 30));
                $rows[] = [
                    'user_id'    => $userId,
                    'song_id'    => $songId,
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('song_favorites')->insertOrIgnore($chunk);
        }
    }

    // ── Sync listens column từ daily stats ───────────────────────────────────

    private function syncListensCounts(array $songIds): void
    {
        foreach ($songIds as $id) {
            $total = DB::table('song_daily_stats')->where('song_id', $id)->sum('play_count');
            DB::table('songs')->where('id', $id)->update(['listens' => (int)$total]);
        }
    }

    // ── Hour weights (phân bố nghe theo giờ trong ngày) ──────────────────────

    private function buildHourWeights(): array
    {
        return [
            0=>1, 1=>1, 2=>1, 3=>1, 4=>1, 5=>2,
            6=>4, 7=>8, 8=>9, 9=>7, 10=>6, 11=>7,
            12=>10, 13=>9, 14=>6, 15=>5, 16=>6, 17=>7,
            18=>9, 19=>11, 20=>13, 21=>14, 22=>11, 23=>8,
        ];
    }

    private function weightedRandom(array $weights): int
    {
        $total = array_sum($weights);
        $r     = rand(1, $total);
        $sum   = 0;
        foreach ($weights as $key => $w) {
            $sum += $w;
            if ($r <= $sum) return $key;
        }
        return array_key_last($weights);
    }
}
