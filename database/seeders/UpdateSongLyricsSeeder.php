<?php

namespace Database\Seeders;

use App\Models\Song;
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
                $song = Song::whereHas('artist', function ($q) use ($artistName) {
                    $q->where('name', $artistName);
                })
                ->where('title', $title)
                ->where('file_path', 'like', '%custom%')
                ->first();

                if (!$song) {
                    $skipped++;
                    continue;
                }

                // Only update if CSV has lyrics and song doesn't
                if (!empty($lyrics) && (empty($song->lyrics) || $song->lyrics !== $lyrics)) {
                    $song->update([
                        'lyrics' => $lyrics,
                        'lyrics_type' => $songData['lyrics_type'] ?? 'plain',
                    ]);
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
            ->whereNotNull('lyrics')
            ->where('lyrics', '!=', '')
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
}
