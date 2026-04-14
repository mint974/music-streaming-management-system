<?php

namespace Database\Seeders;

use App\Models\Song;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MinhTanActivitySeeder extends Seeder
{
    private const TARGET_EMAIL = 'minhtan090704@gmail.com';

    public function run(): void
    {
        $user = User::query()->where('email', self::TARGET_EMAIL)->first();

        if (! $user) {
            $this->command?->warn('Khong tim thay user minhtan090704@gmail.com. Hay chay MinhTanUserSeeder truoc.');
            return;
        }

        $songs = Song::query()
            ->published()
            ->where('is_vip', false)
            ->with(['artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at', 'artistProfile.user:id,name,avatar'])
            ->get(['id', 'artist_profile_id', 'genre_id', 'duration', 'title']);

        if ($songs->isEmpty()) {
            $songs = Song::query()
                ->where('deleted', false)
                ->whereNotNull('file_path')
                ->where('file_path', '!=', '')
                ->where('is_vip', false)
                ->with(['artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at', 'artistProfile.user:id,name,avatar'])
                ->get(['id', 'artist_profile_id', 'genre_id', 'duration', 'title']);
        }

        if ($songs->isEmpty()) {
            $this->command?->warn('Khong co bai hat de tao du lieu lich su/yêu thich.');
            return;
        }

        $this->seedListeningHistory($user->id, $songs);
        $this->seedFavorites($user->id, $songs);

        $historyCount = DB::table('listening_histories')->where('user_id', $user->id)->count();
        $favoriteCount = DB::table('song_favorites')->where('user_id', $user->id)->count();

        $this->command?->info("MinhTanActivitySeeder done: listening_histories={$historyCount}, song_favorites={$favoriteCount}");
    }

    private function seedListeningHistory(int $userId, $songs): void
    {
        DB::table('listening_histories')->where('user_id', $userId)->delete();

        $hasPlayedSeconds = Schema::hasColumn('listening_histories', 'played_seconds');
        $hasPlayedPercent = Schema::hasColumn('listening_histories', 'played_percent');
        $hasIsCompleted = Schema::hasColumn('listening_histories', 'is_completed');
        $rangeStart = Carbon::create(2026, 1, 1, 0, 0, 0);
        $rangeEnd = Carbon::create(2026, 4, 11, 23, 59, 59);
        $totalDays = $rangeStart->diffInDays($rangeEnd) + 1;

        $songPool = $songs->values()->all();
        $songCount = count($songPool);

        $preferredArtistIds = $songs->pluck('artist_profile_id')->filter()->unique()->shuffle()->take(min(3, $songs->pluck('artist_profile_id')->unique()->count()))->values()->all();
        $preferredGenreIds = $songs->pluck('genre_id')->filter()->unique()->shuffle()->take(min(4, $songs->pluck('genre_id')->unique()->count()))->values()->all();

        $hourWeights = $this->hourWeights();

        $rows = [];

        for ($dayIndex = 0; $dayIndex < $totalDays; $dayIndex++) {
            $day = $rangeStart->copy()->addDays($dayIndex);
            if ($day->gt($rangeEnd)) {
                break;
            }
            $daysAgo = $rangeEnd->diffInDays($day);
            $base = $daysAgo <= 7 ? $this->randomBetween(2, 6) : $this->randomBetween(0, 4);
            $bonus = ($dayIndex % 7 === 0 || $dayIndex % 7 === 6) ? 1 : 0;
            $playsInDay = $base + $bonus;

            for ($i = 0; $i < $playsInDay; $i++) {
                $song = $songPool[array_rand($songPool)];

                if (! empty($preferredArtistIds) && $this->randomBetween(1, 100) <= 45) {
                    $preferredArtistSongs = array_values(array_filter($songPool, fn ($item) => in_array((int) $item->artist_profile_id, $preferredArtistIds, true)));
                    if (! empty($preferredArtistSongs)) {
                        $song = $preferredArtistSongs[array_rand($preferredArtistSongs)];
                    }
                }

                if (! empty($preferredGenreIds) && $this->randomBetween(1, 100) <= 35) {
                    $preferredGenreSongs = array_values(array_filter($songPool, fn ($item) => in_array((int) $item->genre_id, $preferredGenreIds, true)));
                    if (! empty($preferredGenreSongs)) {
                        $song = $preferredGenreSongs[array_rand($preferredGenreSongs)];
                    }
                }

                $hour = $this->weightedHour($hourWeights);
                $playedAt = $day->copy()->setHour($hour)->setMinute($this->randomBetween(0, 59))->setSecond($this->randomBetween(0, 59));
                if ($playedAt->gt($rangeEnd)) {
                    $playedAt = $rangeEnd->copy()->subSeconds($this->randomBetween(0, 3599));
                }

                $duration = max(30, (int) ($song->duration ?? $this->randomBetween(120, 320)));
                $playedPercent = $this->randomPlayedPercent();
                $playedSeconds = max(5, (int) round($duration * ($playedPercent / 100)));
                $isCompleted = $playedPercent >= 95;

                $row = [
                    'user_id' => $userId,
                    'song_id' => (int) $song->id,
                    'listened_at' => $playedAt->format('Y-m-d H:i:s'),
                    'created_at' => $playedAt->format('Y-m-d H:i:s'),
                    'updated_at' => $playedAt->format('Y-m-d H:i:s'),
                ];

                if ($hasPlayedSeconds) {
                    $row['played_seconds'] = $playedSeconds;
                }
                if ($hasPlayedPercent) {
                    $row['played_percent'] = $playedPercent;
                }
                if ($hasIsCompleted) {
                    $row['is_completed'] = $isCompleted;
                }

                $rows[] = $row;
            }
        }

        // Always ensure enough rows for recommendation and filter testing.
        while (count($rows) < min(280, $songCount * 8)) {
            $song = $songPool[array_rand($songPool)];
            $playedAt = $rangeStart->copy()
                ->addDays($this->randomBetween(0, max(0, $totalDays - 1)))
                ->setHour($this->weightedHour($hourWeights))
                ->setMinute($this->randomBetween(0, 59))
                ->setSecond($this->randomBetween(0, 59));
            if ($playedAt->gt($rangeEnd)) {
                $playedAt = $rangeEnd->copy()->subSeconds($this->randomBetween(0, 3599));
            }

            $duration = max(30, (int) ($song->duration ?? $this->randomBetween(120, 320)));
            $playedPercent = $this->randomPlayedPercent();
            $playedSeconds = max(5, (int) round($duration * ($playedPercent / 100)));
            $isCompleted = $playedPercent >= 95;

            $row = [
                'user_id' => $userId,
                'song_id' => (int) $song->id,
                'listened_at' => $playedAt->format('Y-m-d H:i:s'),
                'created_at' => $playedAt->format('Y-m-d H:i:s'),
                'updated_at' => $playedAt->format('Y-m-d H:i:s'),
            ];

            if ($hasPlayedSeconds) {
                $row['played_seconds'] = $playedSeconds;
            }
            if ($hasPlayedPercent) {
                $row['played_percent'] = $playedPercent;
            }
            if ($hasIsCompleted) {
                $row['is_completed'] = $isCompleted;
            }

            $rows[] = $row;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('listening_histories')->insert($chunk);
        }
    }

    private function seedFavorites(int $userId, $songs): void
    {
        DB::table('song_favorites')->where('user_id', $userId)->delete();

        $topSongIds = DB::table('listening_histories')
            ->where('user_id', $userId)
            ->selectRaw('song_id, COUNT(*) as c')
            ->groupBy('song_id')
            ->orderByDesc('c')
            ->limit(30)
            ->pluck('song_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $allSongIds = $songs->pluck('id')->map(fn ($id) => (int) $id)->all();
        shuffle($allSongIds);

        $picked = array_values(array_unique(array_merge($topSongIds, array_slice($allSongIds, 0, 20))));
        $picked = array_slice($picked, 0, 40);

        $rows = [];
        $now = Carbon::now();
        foreach ($picked as $songId) {
            $ts = $now->copy()->subDays($this->randomBetween(0, 100))->subHours($this->randomBetween(0, 23));
            $rows[] = [
                'user_id' => $userId,
                'song_id' => $songId,
                'created_at' => $ts->format('Y-m-d H:i:s'),
                'updated_at' => $ts->format('Y-m-d H:i:s'),
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('song_favorites')->insertOrIgnore($chunk);
        }
    }

    private function hourWeights(): array
    {
        return [
            0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 2,
            6 => 4, 7 => 7, 8 => 9, 9 => 8, 10 => 7, 11 => 7,
            12 => 8, 13 => 8, 14 => 7, 15 => 6, 16 => 7, 17 => 8,
            18 => 10, 19 => 12, 20 => 14, 21 => 15, 22 => 12, 23 => 8,
        ];
    }

    private function weightedHour(array $weights): int
    {
        $total = array_sum($weights);
        $random = $this->randomBetween(1, $total);
        $acc = 0;

        foreach ($weights as $hour => $weight) {
            $acc += $weight;
            if ($random <= $acc) {
                return (int) $hour;
            }
        }

        return 21;
    }

    private function randomPlayedPercent(): float
    {
        $roll = $this->randomBetween(1, 100);
        if ($roll <= 25) {
            return (float) $this->randomBetween(35, 69);
        }
        if ($roll <= 70) {
            return (float) $this->randomBetween(70, 94);
        }

        return (float) $this->randomBetween(95, 100);
    }

    private function randomBetween(int $min, int $max): int
    {
        return (int) collect(range($min, $max))->random();
    }
}
