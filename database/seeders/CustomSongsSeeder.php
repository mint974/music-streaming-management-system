<?php

namespace Database\Seeders;

use App\Models\ArtistPackage;
use App\Models\ArtistProfile;
use App\Models\Genre;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CustomSongsSeeder extends Seeder
{
    private string $csvFile;
    private array $genreMap = [];
    private array $tagMap = [];

    public function __construct()
    {
        $this->csvFile = database_path('seeders/data_custom/songs_with_metadata.csv');
    }

    public function run(): void
    {
        // ── Kiểm tra file CSV ─────────────────────────────────────────────────
        if (!file_exists($this->csvFile)) {
            $this->command->error('❌ File không tồn tại: ' . $this->csvFile);
            $this->command->newLine();
            $this->command->info('👉 Hãy chuẩn bị file CSV metadata tại:');
            $this->command->line('   database/seeders/data_custom/songs_with_metadata.csv');
            return;
        }

        $this->command->info('🎵 Đang import Custom Vietnamese Songs dataset...');
        $this->command->info('   File: ' . basename($this->csvFile));
        $this->command->newLine();

        // ── Build maps ────────────────────────────────────────────────────────
        // Build genre map with case-insensitive keys for matching
        $genres = Genre::select('id', 'name')->get();
        $this->genreMap = [];
        foreach ($genres as $genre) {
            // Store with original name and lowercase name for flexible matching
            $this->genreMap[$genre->name] = $genre->id;
            $this->genreMap[strtolower($genre->name)] = $genre->id;
        }
        
        $this->tagMap = Tag::pluck('id', 'slug')->all();

        // ── Read CSV ──────────────────────────────────────────────────────────
        $csvData = $this->readCsvFile();

        if (empty($csvData)) {
            $this->command->error('❌ CSV file rỗng hoặc không đọc được.');
            return;
        }

        $this->command->info("📊 Tổng số bài: {$csvData['count']}");
        $this->command->newLine();

        // ── Process in transaction ────────────────────────────────────────────
        DB::transaction(function () use ($csvData) {

            // Step 1: Create/find artists
            $this->command->getOutput()->write('   👤 Processing artists... ');
            $artistMap = $this->processArtists($csvData['songs']);
            $this->command->getOutput()->writeln('<info>✅ ' . count($artistMap) . '</info>');

            // Step 2: Create albums (if specified)
            $this->command->getOutput()->write('   💿 Processing albums... ');
            $albumMap = $this->processAlbums($csvData['songs'], $artistMap);
            $this->command->getOutput()->writeln('<info>✅ ' . count($albumMap) . '</info>');

            // Step 3: Create tags
            $this->command->getOutput()->write('   🏷️  Processing tags... ');
            $tagsCreated = $this->processTags($csvData['songs']);
            $this->command->getOutput()->writeln('<info>✅ ' . $tagsCreated . '</info>');

            // Update tag map after creating new tags
            $this->tagMap = Tag::pluck('id', 'slug')->all();

            // Step 4: Insert songs
            $this->command->getOutput()->write('   🎵 Inserting songs... ');
            $songIds = $this->insertSongs($csvData['songs'], $artistMap, $albumMap);
            $this->command->getOutput()->writeln('<info>✅ ' . count($songIds) . '</info>');

            // Step 5: Attach tags to songs
            $this->command->getOutput()->write('   🔗 Attaching tags... ');
            $tagsAttached = $this->attachTagsToSongs($csvData['songs'], $songIds);
            $this->command->getOutput()->writeln('<info>✅ ' . $tagsAttached . '</info>');

            // Step 6: Sync lyrics into song_lyrics structure for lyric-version features
            $this->command->getOutput()->write('   📝 Syncing lyric versions... ');
            $lyricsSynced = $this->syncSongLyrics($csvData['songs'], $songIds);
            $this->command->getOutput()->writeln('<info>✅ ' . $lyricsSynced . '</info>');

        });

        $this->command->newLine();
        $this->command->info('✅ Import hoàn tất!');
        $this->command->info('   Bài hát đã được thêm vào database và sẵn sàng hiển thị trên website.');
        $this->command->newLine();
        $this->command->info('📋 Kiểm tra:');
        $this->command->line('   php artisan tinker');
        $this->command->line("   >>> Song::where('file_path', 'like', '%custom%')->count()");
        $this->command->line("   >>> Song::where('file_path', 'like', '%custom%')->first()");
    }

    /**
     * Đọc file CSV và trả về mảng songs
     */
    private function readCsvFile(): array
    {
        $songs = [];
        $handle = fopen($this->csvFile, 'r');

        if ($handle === false) {
            return ['count' => 0, 'songs' => []];
        }

        // Read header
        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);
            return ['count' => 0, 'songs' => []];
        }

        // Read all rows
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
     * Tạo hoặc tìm artists từ CSV.
     *
     * Returns:
     * ['Phương Thanh' => ['user_id' => 10, 'artist_profile_id' => 5], ...]
     */
    private function processArtists(array $songs): array
    {
        $artistNames = array_unique(array_column($songs, 'artist'));
        $artistMap = [];

        foreach ($artistNames as $name) {
            if (empty($name)) {
                continue;
            }

            // Tìm artist đã tồn tại
            $user = User::where('name', $name)
                ->whereHas('roles', fn($q) => $q->where('slug', 'artist'))
                ->first();

            // Nếu chưa có, tạo mới
            if (!$user) {
                $email = Str::slug($name) . '@custom-artist.local';
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'status' => 'Đang hoạt động',
                    'email_verified_at' => now(),
                ]);
                $user->syncRoles(['artist']);
            }

            $package = ArtistPackage::query()
                ->where('is_active', true)
                ->orderByDesc('id')
                ->first();

            if ($package) {
                $now = now();
                $startDate = $now;
                $endDate = $now->copy()->addDays((int) ($package->duration_days ?? 365));

                ArtistProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'artist_package_id' => $package->id,
                        'stage_name' => $name,
                        'bio' => null,
                        'avatar' => $user->avatar,
                        'cover_image' => null,
                        'verified_at' => $startDate,
                        'status' => \App\Models\ArtistProfile::STATUS_ACTIVE,
                        'revoked_at' => null,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]
                );
            }

            $profileId = (int) ArtistProfile::query()
                ->where('user_id', $user->id)
                ->value('id');

            $artistMap[$name] = [
                'user_id' => (int) $user->id,
                'artist_profile_id' => $profileId,
            ];
        }

        return $artistMap;
    }

    /**
     * Tạo albums từ CSV (nếu có)
     * Returns: ['Album Name' => album_id, ...]
     */
    private function processAlbums(array $songs, array $artistMap): array
    {
        $albumMap = [];
        $albumsToCreate = [];

        foreach ($songs as $song) {
            $albumName = trim($song['album'] ?? '');
            $artistName = $song['artist'] ?? '';

            if (empty($albumName) || empty($artistName)) {
                continue;
            }

            // Skip if already processed
            if (isset($albumMap[$albumName])) {
                continue;
            }

            $artistInfo = $artistMap[$artistName] ?? null;
            $userId = (int) ($artistInfo['user_id'] ?? 0);
            $artistProfileId = (int) ($artistInfo['artist_profile_id'] ?? 0);

            if ($userId <= 0 || $artistProfileId <= 0) {
                continue;
            }

            // Check if album already exists
            $album = DB::table('albums')
                ->where('title', $albumName)
                ->where('artist_profile_id', $artistProfileId)
                ->first();

            if ($album) {
                $albumMap[$albumName] = $album->id;
            } else {
                $albumsToCreate[] = [
                    'artist_profile_id' => $artistProfileId,
                    'title' => $albumName,
                    'description' => '',
                    'cover_image' => null,
                    'released_date' => $song['released_date'] ?? null,
                    'status' => 'published',
                    'deleted' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert new albums
        if (!empty($albumsToCreate)) {
            DB::table('albums')->insert($albumsToCreate);

            // Get newly created album IDs
            foreach ($albumsToCreate as $album) {
                $created = DB::table('albums')
                    ->where('title', $album['title'])
                    ->where('artist_profile_id', $album['artist_profile_id'])
                    ->first();

                if ($created) {
                    $albumMap[$album['title']] = $created->id;
                }
            }
        }

        return $albumMap;
    }

    /**
     * Tạo tags từ CSV (mood, activity, topic)
     * Returns: số lượng tags mới được tạo
     */
    private function processTags(array $songs): int
    {
        $allTags = [];

        foreach ($songs as $song) {
            // Parse mood tags
            $moodTags = $this->parseTags($song['mood_tags'] ?? '');
            foreach ($moodTags as $slug) {
                $allTags[$slug] = 'mood';
            }

            // Parse activity tags
            $activityTags = $this->parseTags($song['activity_tags'] ?? '');
            foreach ($activityTags as $slug) {
                $allTags[$slug] = 'activity';
            }

            // Parse topic tags
            $topicTags = $this->parseTags($song['topic_tags'] ?? '');
            foreach ($topicTags as $slug) {
                $allTags[$slug] = 'topic';
            }
        }

        $tagsCreated = 0;

        foreach ($allTags as $slug => $type) {
            // Check if tag already exists
            $exists = Tag::where('slug', $slug)->exists();

            if (!$exists) {
                Tag::create([
                    'label' => ucfirst(str_replace('-', ' ', $slug)),
                    'slug' => $slug,
                    'type' => $type,
                ]);
                $tagsCreated++;
            }
        }

        return $tagsCreated;
    }

    /**
     * Parse tag string (semicolon-separated) thành array
     */
    private function parseTags(string $tagString): array
    {
        if (empty($tagString)) {
            return [];
        }

        return array_filter(
            array_map('trim', explode(';', $tagString)),
            fn ($tag) => !empty($tag)
        );
    }

    /**
     * Get genre ID from genre name with smart mapping
     * - Case-insensitive matching
     * - Maps "Rap" to "Rap / Hip-hop"
     */
    private function getGenreId(string $genreName): ?int
    {
        if (empty($genreName)) {
            return null;
        }

        $genreName = trim($genreName);
        
        // Special mapping for "Rap" → "Rap / Hip-hop"
        if (strtolower($genreName) === 'rap') {
            $genreName = 'Rap / Hip-hop';
        }

        // Try exact match first
        if (isset($this->genreMap[$genreName])) {
            return $this->genreMap[$genreName];
        }

        // Try case-insensitive match
        $lowercase = strtolower($genreName);
        if (isset($this->genreMap[$lowercase])) {
            return $this->genreMap[$lowercase];
        }

        // Try fuzzy match for partial matches
        foreach ($this->genreMap as $dbGenre => $id) {
            if (strtolower($dbGenre) === $lowercase) {
                return $id;
            }
        }

        return null;
    }

    /**
     * Insert songs vào database
     * Returns: [csv_row_index => song_id]
     */
    private function insertSongs(array $songs, array $artistMap, array $albumMap): array
    {
        $songIds = [];
        $now = now()->toDateTimeString();

        foreach ($songs as $index => $song) {
            $artistName = $song['artist'] ?? '';
            $artistInfo = $artistMap[$artistName] ?? null;
            $userId = (int) ($artistInfo['user_id'] ?? 0);
            $artistProfileId = (int) ($artistInfo['artist_profile_id'] ?? 0);

            if ($userId <= 0 || $artistProfileId <= 0) {
                $this->command->warn("⚠️  Bỏ qua '{$song['title']}' - không tìm thấy artist");
                continue;
            }

            // Get genre_id using smart matching
            $genreId = $this->getGenreId($song['genre'] ?? '');

            // Get album_id
            $albumName = trim($song['album'] ?? '');
            $albumId = !empty($albumName) ? ($albumMap[$albumName] ?? null) : null;

            // Parse is_vip
            $isVip = isset($song['is_vip']) && ($song['is_vip'] === '1' || $song['is_vip'] === 'true');

            // Parse status
            $status = $song['status'] ?? 'published';
            if (!in_array($status, ['draft', 'pending', 'published'])) {
                $status = 'published';
            }

            $payload = [
                'artist_profile_id' => $artistProfileId,
                'genre_id' => $genreId,
                'album_id' => $albumId,
                'title' => mb_substr($song['title'], 0, 255),
                'author' => mb_substr($song['author'] ?? '', 0, 150),
                'duration' => (int) ($song['duration'] ?? 0),
                'file_path' => $song['file_path'] ?? null,
                'file_mime' => $song['file_mime'] ?? 'audio/mpeg',
                'file_size' => (int) ($song['file_size'] ?? 0),
                'cover_image' => $song['cover_image'] ?? null,
                'lyrics' => $song['lyrics'] ?? null,
                'lyrics_type' => ($song['lyrics_type'] ?? 'plain'),
                'released_date' => $song['released_date'] ?? null,
                'is_vip' => $isVip,
                'status' => $status,
                'listens' => (int) ($song['listens'] ?? 0),
                'deleted' => 0,
                'updated_at' => $now,
            ];

            $filePath = trim((string) ($song['file_path'] ?? ''));

            if ($filePath !== '') {
                $existingId = DB::table('songs')->where('file_path', $filePath)->value('id');

                if ($existingId) {
                    DB::table('songs')->where('id', $existingId)->update($payload);
                    $songIds[$index] = (int) $existingId;
                } else {
                    $payload['created_at'] = $now;
                    $songIds[$index] = (int) DB::table('songs')->insertGetId($payload);
                }

                continue;
            }

            // Fallback key nếu không có file_path
            $existingId = DB::table('songs')
                ->where('title', $payload['title'])
                ->where('artist_profile_id', $artistProfileId)
                ->value('id');

            if ($existingId) {
                DB::table('songs')->where('id', $existingId)->update($payload);
                $songIds[$index] = (int) $existingId;
            } else {
                $payload['created_at'] = $now;
                $songIds[$index] = (int) DB::table('songs')->insertGetId($payload);
            }
        }

        return $songIds;
    }

    /**
     * Attach tags to songs
     * Returns: số lượng tags được attach
     */
    private function attachTagsToSongs(array $songs, array $songIds): int
    {
        $tagAttachments = [];
        $uniqueSongIds = array_values(array_unique(array_filter(array_values($songIds))));

        if (!empty($uniqueSongIds)) {
            // Đồng bộ tags theo CSV: xóa tag cũ của các bài custom trước khi attach lại.
            DB::table('song_tags')->whereIn('song_id', $uniqueSongIds)->delete();
        }

        foreach ($songs as $index => $song) {
            $songId = $songIds[$index] ?? null;

            if (!$songId) {
                continue;
            }

            // Get all tags for this song
            $allTags = [];

            $moodTags = $this->parseTags($song['mood_tags'] ?? '');
            $activityTags = $this->parseTags($song['activity_tags'] ?? '');
            $topicTags = $this->parseTags($song['topic_tags'] ?? '');

            $allTags = array_merge($moodTags, $activityTags, $topicTags);

            foreach ($allTags as $slug) {
                $tagId = $this->tagMap[$slug] ?? null;

                if ($tagId) {
                    $tagAttachments[] = [
                        'song_id' => $songId,
                        'tag_id' => $tagId,
                    ];
                }
            }
        }

        // Remove duplicates
        $tagAttachments = array_unique($tagAttachments, SORT_REGULAR);

        // Insert in chunks
        if (!empty($tagAttachments)) {
            foreach (array_chunk($tagAttachments, 500) as $chunk) {
                DB::table('song_tags')->insertOrIgnore($chunk);
            }
        }

        return count($tagAttachments);
    }

    /**
     * Đồng bộ lyrics text từ CSV sang bảng song_lyrics / song_lyric_lines.
     */
    private function syncSongLyrics(array $songs, array $songIds): int
    {
        if (! Schema::hasTable('song_lyrics')) {
            return 0;
        }

        $hasName = Schema::hasColumn('song_lyrics', 'name');
        $hasVisible = Schema::hasColumn('song_lyrics', 'is_visible');
        $syncedCount = 0;

        foreach ($songs as $index => $songData) {
            $songId = $songIds[$index] ?? null;
            if (! $songId) {
                continue;
            }

            $lyricsText = trim((string) ($songData['lyrics'] ?? ''));
            if ($lyricsText === '') {
                continue;
            }

            $rawType = strtolower(trim((string) ($songData['lyrics_type'] ?? 'plain')));
            $type = in_array($rawType, ['lrc', 'synced'], true) ? 'synced' : 'plain';

            $existingLyric = DB::table('song_lyrics')
                ->where('song_id', $songId)
                ->orderByDesc('is_default')
                ->orderByDesc('id')
                ->first();

            $payload = [
                'song_id' => $songId,
                'language_code' => 'vi',
                'type' => $type,
                'source' => 'artist',
                'status' => 'verified',
                'raw_text' => $lyricsText,
                'is_default' => true,
                'verified_at' => now(),
                'updated_at' => now(),
            ];

            if ($hasName) {
                $payload['name'] = $type === 'synced' ? 'Lời đồng bộ #1' : 'Lời thường #1';
            }

            if ($hasVisible) {
                $payload['is_visible'] = true;
            }

            DB::table('song_lyrics')->where('song_id', $songId)->update(['is_default' => false]);

            if ($existingLyric) {
                DB::table('song_lyrics')->where('id', $existingLyric->id)->update($payload);
                $lyricId = (int) $existingLyric->id;
            } else {
                $payload['created_at'] = now();
                $lyricId = (int) DB::table('song_lyrics')->insertGetId($payload);
            }

            DB::table('songs')->where('id', $songId)->update([
                'has_lyrics' => true,
                'default_lyric_id' => $lyricId,
                'updated_at' => now(),
            ]);

            if ($type === 'synced' && Schema::hasTable('song_lyric_lines')) {
                $lines = $this->parseLrcLines($lyricsText);
                DB::table('song_lyric_lines')->where('song_lyric_id', $lyricId)->delete();

                if (! empty($lines)) {
                    DB::table('song_lyric_lines')->insert(array_map(function (array $line) use ($lyricId) {
                        return [
                            'song_lyric_id' => $lyricId,
                            'line_order' => $line['line_order'],
                            'start_time_ms' => $line['start_time_ms'],
                            'end_time_ms' => null,
                            'content' => $line['content'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }, $lines));
                }
            }

            $syncedCount++;
        }

        return $syncedCount;
    }

    /**
     * Parse LRC thành các line đồng bộ time.
     *
     * @return array<int, array{line_order:int,start_time_ms:int,content:string}>
     */
    private function parseLrcLines(string $rawText): array
    {
        $rows = preg_split('/\r\n|\r|\n/', $rawText) ?: [];
        $parsed = [];
        $order = 0;

        foreach ($rows as $row) {
            $row = trim($row);
            if ($row === '') {
                continue;
            }

            if (! preg_match('/^\[(\d{2}):(\d{2})(?:\.(\d{2,3}))?\](.*)$/', $row, $matches)) {
                continue;
            }

            $minute = (int) $matches[1];
            $second = (int) $matches[2];
            $millisecond = isset($matches[3]) && $matches[3] !== ''
                ? (int) str_pad($matches[3], 3, '0', STR_PAD_RIGHT)
                : 0;
            $content = trim((string) ($matches[4] ?? ''));

            $parsed[] = [
                'line_order' => $order++,
                'start_time_ms' => (($minute * 60) + $second) * 1000 + $millisecond,
                'content' => $content,
            ];
        }

        return $parsed;
    }
}
