<?php

namespace App\Console\Commands;

use App\Models\Song;
use App\Models\SongLyric;
use App\Models\SongLyricLine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateLyricsToNewStructure extends Command
{
    protected $signature = 'lyrics:migrate-to-new-structure';
    protected $description = 'Migrate existing raw lyrics from songs table to the new normalized song_lyrics and song_lyric_lines tables';

    public function handle()
    {
        if (! Schema::hasColumn('songs', 'lyrics')) {
            $this->warn('Skipping: songs.lyrics column no longer exists. Lyrics are already normalized in song_lyrics.');
            return self::SUCCESS;
        }

        $songs = Song::whereNotNull('lyrics')->where('lyrics', '!=', '')->get();
        $this->info("Found {$songs->count()} songs with existing lyrics to migrate.");

        DB::beginTransaction();
        try {
            foreach ($songs as $song) {
                $this->line("Migrating song ID {$song->id} ({$song->title})...");
                
                $type = $song->lyrics_type === 'lrc' ? 'synced' : 'plain';
                
                $songLyric = SongLyric::create([
                    'song_id' => $song->id,
                    'name' => $type === 'synced' ? 'Lời đồng bộ (migrate)' : 'Lời thường (migrate)',
                    'language_code' => 'vi',
                    'source' => 'import',
                    'is_default' => true,
                    'is_visible' => true,
                ]);

                if ($type === 'synced') {
                    $lines = explode("\n", $song->lyrics);
                    $lineOrder = 1;
                    $linesToInsert = [];
                    foreach ($lines as $line) {
                        if (preg_match('/\[(\d{2,}):(\d{2})(?:\.(\d{1,3}))?\](.*)/', $line, $matches)) {
                            $min = (int) $matches[1];
                            $sec = (int) $matches[2];
                            $msStr = isset($matches[3]) && $matches[3] !== '' ? $matches[3] : '0';
                            
                            $msParts = (int) $msStr;
                            if (strlen($msStr) === 1) {
                                $msParts *= 100;
                            } elseif (strlen($msStr) === 2) {
                                $msParts *= 10;
                            }
                            
                            $timeMs = ($min * 60 * 1000) + ($sec * 1000) + $msParts;
                            $text = trim($matches[4]);

                            if (!empty($text)) {
                                $linesToInsert[] = [
                                    'song_lyric_id' => $songLyric->id,
                                    'line_order' => $lineOrder++,
                                    'start_time_ms' => $timeMs,
                                    'end_time_ms' => null,
                                    'content' => $text,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }
                    
                    if (!empty($linesToInsert)) {
                        SongLyricLine::insert($linesToInsert);
                    }
                } else {
                    $rows = preg_split('/\r\n|\r|\n/', (string) $song->lyrics) ?: [];
                    $lineOrder = 1;
                    $linesToInsert = [];

                    foreach ($rows as $row) {
                        $text = trim((string) $row);
                        if ($text === '') {
                            continue;
                        }

                        $linesToInsert[] = [
                            'song_lyric_id' => $songLyric->id,
                            'line_order' => $lineOrder++,
                            'start_time_ms' => null,
                            'end_time_ms' => null,
                            'content' => $text,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (!empty($linesToInsert)) {
                        SongLyricLine::insert($linesToInsert);
                    }
                }

                SongLyric::where('song_id', $song->id)
                    ->where('id', '!=', $songLyric->id)
                    ->update(['is_default' => false]);

                // Important: turn off timestamps so we don't accidentally mark the song as recently updated if tracking changes.
                $song->timestamps = false;
                $song->save();
            }
            DB::commit();
            $this->info('Migration completed successfully.');
            return self::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred during migration: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
