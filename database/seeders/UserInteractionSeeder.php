<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Genre;

class UserInteractionSeeder extends Seeder
{
    protected $hits = [];
    protected $tails = [];
    protected $genreMap = [];

    protected $songDurations = [];

    public function run()
    {
        $this->command->info("Initializing Taste-based User Interaction Seeder...");

        // Chuẩn bị dữ liệu bài hát phân hóa Hits (10%) & Tails (90%)
        $genres = DB::table('genres')->get();
        foreach ($genres as $genre) {
            $this->genreMap[strtolower(trim($genre->name))] = $genre->id;
            
            $songs = DB::table('songs')->where('genre_id', $genre->id)->where('status', 'published')->get();
            if ($songs->isEmpty()) continue;

            $totalSongs = $songs->count();
            $hitCount = max(1, (int)($totalSongs * 0.10));
            
            foreach ($songs as $song) {
                // Khởi tạo thời lượng ngẫu nhiên nếu không có sẵn, mặc định 240s
                $this->songDurations[$song->id] = $song->duration > 0 ? $song->duration : 240;
            }

            $this->hits[$genre->id] = $songs->take($hitCount)->pluck('id')->toArray();
            $this->tails[$genre->id] = $songs->slice($hitCount)->pluck('id')->toArray();
            
            // Xử lý nếu thể loại chỉ có 1 bài
            if (empty($this->tails[$genre->id])) {
                $this->tails[$genre->id] = $this->hits[$genre->id];
            }
        }

        // Định danh Persona
        $personaMap = [
            'A' => ['pop', 'ballad'], 
            'B' => ['edm', 'rock', 'rap', 'hip-hop'],
            'C' => ['bolero', 'trữ tình', 'vàng'], 
            'D' => [] // Thích tất cả
        ];

        // Xóa Users Persona cũ nếu có
        $fakeUserIds = DB::table('users')->where('email', 'like', 'persona_%@test.com')->pluck('id')->toArray();
        if (!empty($fakeUserIds)) {
            DB::table('listening_histories')->whereIn('user_id', $fakeUserIds)->delete();
            DB::table('song_favorites')->whereIn('user_id', $fakeUserIds)->delete();
            DB::table('users')->whereIn('id', $fakeUserIds)->delete();
        }

        $allGenreIds = Genre::pluck('id')->toArray();
        $usersData = [];
        $pwd = Hash::make('123456');
        $now = now();

        $this->command->info("Creating 100 Persona Users...");
        
        for ($i = 1; $i <= 100; $i++) {
            $rand = rand(1, 100);
            if ($rand <= 40) {
                $persona = 'A';
            } elseif ($rand <= 60) {
                $persona = 'B';
            } elseif ($rand <= 80) {
                $persona = 'C';
            } else {
                $persona = 'D';
            }

            $userId = DB::table('users')->insertGetId([
                'name' => "Persona {$persona} User {$i}",
                'email' => "persona_{$persona}_{$i}@test.com",
                'password' => $pwd,
                'status' => 'Đang hoạt động',
                'created_at' => $now,
                'updated_at' => $now
            ]);

            // Map favorite genres logic
            $favGenreIds = [];
            if ($persona === 'D') {
                $favGenreIds = $allGenreIds;
            } else {
                foreach ($personaMap[$persona] as $gName) {
                    foreach ($this->genreMap as $realName => $gid) {
                        if (str_contains($realName, $gName)) {
                            $favGenreIds[] = $gid;
                        }
                    }
                }
                if (empty($favGenreIds)) $favGenreIds = $allGenreIds;
            }

            $usersData[] = [
                'id' => $userId,
                'favGenreIds' => array_unique($favGenreIds)
            ];
        }

        $this->command->info("Simulating Interaction behaviors...");
        $listenHistories = [];
        $favorites = [];

        foreach ($usersData as $userData) {
            $userId = $userData['id'];
            $favGenreIds = $userData['favGenreIds'];

            $interactionsCount = rand(20, 50);
            $seenSongs = [];

            for ($j = 0; $j < $interactionsCount; $j++) {
                $isInTaste = (rand(1, 100) <= 80);

                if ($isInTaste && !empty($favGenreIds)) {
                    $targetGenreId = $favGenreIds[array_rand($favGenreIds)];
                    $playedPercent = rand(70, 100);
                    $listenCount = rand(2, 10);
                    $isFavorite = (rand(1, 100) <= 40); // 40% will favorite
                } else {
                    $targetGenreId = $allGenreIds[array_rand($allGenreIds)];
                    $playedPercent = rand(5, 25);
                    $listenCount = 1;
                    $isFavorite = false;
                }

                $isHit = (rand(1, 100) <= 70); // 70% chance to hear a hit
                $songId = $this->getWeightedSong($targetGenreId, $isHit);
                if (!$songId) continue;

                $songDuration = $this->songDurations[$songId] ?? 240;

                for ($k = 0; $k < $listenCount; $k++) {
                    // Để giữ tính chất phân cực (Taste-based) nhưng vẫn linh hoạt:
                    // Ta giả lập số percent theo khung đúng/sai gu của vòng lặp bên trên.
                    $isCompleted = ($playedPercent >= 90) ? 1 : 0;
                    $playedSeconds = (int) round(($songDuration * $playedPercent) / 100);

                    $listenHistories[] = [
                        'user_id' => $userId,
                        'song_id' => $songId,
                        'played_percent' => $playedPercent,
                        'played_seconds' => $playedSeconds,
                        'is_completed' => $isCompleted,
                        'listened_at' => now()->subDays(rand(0, 30))->subMinutes(rand(0, 1440))->toDateTimeString(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                if ($isFavorite && !isset($seenSongs[$songId])) {
                    $favorites[] = [
                        'user_id' => $userId,
                        'song_id' => $songId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $seenSongs[$songId] = true;
                }
            }

            if (count($listenHistories) >= 1000) {
                DB::table('listening_histories')->insert($listenHistories);
                $listenHistories = [];
            }
        }

        if (count($listenHistories) > 0) {
            DB::table('listening_histories')->insert($listenHistories);
        }
        
        foreach (array_chunk($favorites, 200) as $chunk) {
            DB::table('song_favorites')->insertOrIgnore($chunk);
        }

        $this->command->info("KNN User Interaction Seeding completed!");
    }

    /**
     * Lấy bài hát theo tỷ lệ 70/30 (Hits/Tail)
     */
    protected function getWeightedSong($genreId, $isHit)
    {
        if ($isHit && !empty($this->hits[$genreId])) {
            return $this->hits[$genreId][array_rand($this->hits[$genreId])];
        } elseif (!empty($this->tails[$genreId])) {
            return $this->tails[$genreId][array_rand($this->tails[$genreId])];
        } elseif (!empty($this->hits[$genreId])) {
             return $this->hits[$genreId][array_rand($this->hits[$genreId])];
        }
        return null;
    }
}
