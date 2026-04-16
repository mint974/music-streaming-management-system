<?php

namespace Database\Seeders;

use App\Models\Song;
use App\Models\SongLyric;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateSongLyricsSeeder extends Seeder
{
    private string $csvFile;

    public function __construct()
    {
        $this->csvFile = database_path('seeders/data/songs_with_lyrics.csv');
    }

    public function run(): void
    {
        // ── Kiểm tra file CSV ─────────────────────────────────────────────────
        if (!file_exists($this->csvFile)) {
            $this->command->warn('⚠️  Bỏ qua UpdateSongLyricsSeeder: chưa có file ' . basename($this->csvFile));
            $this->command->line('   (Tùy chọn) Thêm file: database/seeders/data/songs_with_lyrics.csv');
            return;
        }

        $this->command->info('🎤 Đang update lyrics cho Custom Songs...');
        $this->command->info('   File: ' . basename($this->csvFile));
        $this->command->newLine();

        // ── Read CSV ──────────────────────────────────────────────────────────
        $csvData = $this->readCsvFile();

        if (empty($csvData)) {
            $this->command->error('❌ CSV file rỗng hoặc không đọc được.');
            return;
        }

        $this->command->info("📊 Tổng số bài trong CSV: {$csvData['count']}");
        $this->command->newLine();

        // ── Update lyrics ─────────────────────────────────────────────────────
        $this->command->getOutput()->write('   🎤 Updating lyrics... ');

        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($csvData, &$updated, &$skipped) {
            foreach ($csvData['songs'] as $songData) {
                $title = $songData['title'] ?? '';
                $artistName = $songData['artist'] ?? '';
                $lyrics = trim($songData['lyrics'] ?? '');

                if (empty($title) || empty($artistName)) {
                    $skipped++;
                    continue;
                }

                // Find song by title and artist
                $song = Song::whereHas('artistProfile.user', function ($q) use ($artistName) {
                    $q->where('name', $artistName);
                })
                ->where('title', $title)
                ->where('file_path', 'like', '%custom%')
                ->first();

                if (!$song) {
                    $skipped++;
                    continue;
                }

                if (!empty($lyrics)) {
                    $rawType = strtolower((string) ($songData['lyrics_type'] ?? 'plain'));
                    $type = $rawType === 'lrc' ? 'synced' : 'plain';

                    $songLyric = SongLyric::where('song_id', $song->id)
                        ->where('is_default', true)
                        ->first()
                        ?? SongLyric::where('song_id', $song->id)->latest('id')->first();

                    if ($songLyric) {
                        $songLyric->update([
                            'name' => $type === 'synced' ? 'Lời đồng bộ #1' : 'Lời thường #1',
                            'language_code' => 'vi',
                            'source' => 'import',
                            'is_default' => true,
                            'is_visible' => true,
                        ]);
                    } else {
                        $songLyric = SongLyric::create([
                            'song_id' => $song->id,
                            'name' => $type === 'synced' ? 'Lời đồng bộ #1' : 'Lời thường #1',
                            'language_code' => 'vi',
                            'source' => 'import',
                            'is_default' => true,
                            'is_visible' => true,
                        ]);
                    }

                    $lines = $type === 'synced'
                        ? $this->parseLrcLines($lyrics)
                        : $this->parsePlainLines($lyrics);

                    $songLyric->lines()->delete();

                    if (!empty($lines)) {
                        $songLyric->lines()->insert(array_map(function (array $line) use ($songLyric) {
                            return [
                                'song_lyric_id' => $songLyric->id,
                                'line_order' => $line['line_order'],
                                'start_time_ms' => $line['start_time_ms'] ?? null,
                                'end_time_ms' => null,
                                'content' => $line['content'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }, $lines));
                    }

                    SongLyric::where('song_id', $song->id)
                        ->where('id', '!=', $songLyric->id)
                        ->update(['is_default' => false]);

                    $updated++;
                } else {
                    $skipped++;
                }
            }
        });

        $this->command->getOutput()->writeln("<info>✅ {$updated}</info>");

        // ── Summary ───────────────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('✅ Update hoàn tất!');
        $this->command->info("   Updated: {$updated} bài");
        $this->command->info("   Skipped: {$skipped} bài");
        $this->command->newLine();

        // ── Verify ────────────────────────────────────────────────────────────
        $totalCustom = Song::where('file_path', 'like', '%custom%')->count();
        $withLyrics = Song::where('file_path', 'like', '%custom%')
            ->whereHas('lyrics', fn ($query) => $query->where('is_default', true))
            ->count();
        $percent = $totalCustom > 0 ? round(($withLyrics / $totalCustom) * 100, 1) : 0;

        $this->command->info('📊 Trạng thái lyrics:');
        $this->command->line("   ✅ Có lyrics: {$withLyrics} / {$totalCustom} bài ({$percent}%)");
        $this->command->line("   ❌ Còn thiếu: " . ($totalCustom - $withLyrics) . " bài");
    }

    /**
     * Đọc file CSV
     */
    private function readCsvFile(): array
    {
        $songs = [];
        $handle = fopen($this->csvFile, 'r');

        if ($handle === false) {
            return ['count' => 0, 'songs' => []];
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);
            return ['count' => 0, 'songs' => []];
        }

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($header)) {
                $songs[] = array_combine($header, $row);
            }
        }

        fclose($handle);

        return [
            'count' => count($songs),
            'songs' => $songs,
        ];
    }

    /**
     * @return array<int, array{line_order:int,start_time_ms:int,content:string}>
     */
    private function parseLrcLines(string $rawText): array
    {
        $rows = \preg_split('/\r\n|\r|\n/', $rawText) ?: [];
        $parsed = [];
        $order = 0;

        foreach ($rows as $row) {
            $row = trim($row);
            if ($row === '') {
                continue;
            }

            if (! \preg_match('/^\[(\d{2}):(\d{2})(?:\.(\d{2,3}))?\](.*)$/', $row, $matches)) {
                continue;
            }

            $minute = (int) $matches[1];
            $second = (int) $matches[2];
            $millisecond = isset($matches[3]) && $matches[3] !== ''
                ? (int) str_pad($matches[3], 3, '0', STR_PAD_RIGHT)
                : 0;
            $content = trim((string) ($matches[4] ?? ''));

            if ($content === '') {
                continue;
            }

            $parsed[] = [
                'line_order' => $order++,
                'start_time_ms' => (($minute * 60) + $second) * 1000 + $millisecond,
                'content' => $content,
            ];
        }

        return $parsed;
    }

    /**
     * @return array<int, array{line_order:int,start_time_ms:null,content:string}>
     */
    private function parsePlainLines(string $rawText): array
    {
        $rows = \preg_split('/\r\n|\r|\n/', $rawText) ?: [];
        $parsed = [];
        $order = 0;

        foreach ($rows as $row) {
            $content = trim((string) $row);
            if ($content === '') {
                continue;
            }

            $parsed[] = [
                'line_order' => $order++,
                'start_time_ms' => null,
                'content' => $content,
            ];
        }

        return $parsed;
    }
}
