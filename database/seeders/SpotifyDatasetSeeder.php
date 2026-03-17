<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpotifyDatasetSeeder extends Seeder
{
    private const FEATURE_COLUMNS = [
        'danceability',
        'energy',
        'valence',
        'acousticness',
        'instrumentalness',
        'speechiness',
        'liveness',
        'tempo',
        'loudness',
    ];

    private string $jsonFile;

    public function __construct()
    {
        $this->jsonFile = database_path('seeders/spotify_seed_data.json');
    }

    public function run(): void
    {
        // ── Kiểm tra file JSON ────────────────────────────────────────────────
        if (!file_exists($this->jsonFile)) {
            $this->command->error('❌ File không tồn tại: ' . $this->jsonFile);
            $this->command->newLine();
            $this->command->info('👉 Chạy Python script để tạo file:');
            $this->command->line('   pip install kagglehub pandas');
            $this->command->line('   python database/seeders/spotify_dataset_converter.py');
            return;
        }

        $data = json_decode(file_get_contents($this->jsonFile), true);

        if (!$data || empty($data['songs'])) {
            $this->command->error('❌ File JSON rỗng hoặc không đúng định dạng.');
            return;
        }

        $this->command->info('📦 Đang import Spotify dataset...');
        $this->command->info("   Generated at: {$data['generated_at']}");
        $this->command->info("   Artists: {$data['counts']['artists']} | Albums: {$data['counts']['albums']} | Songs: {$data['counts']['songs']}");
        $this->command->newLine();

        // ── Build genre name → id map ─────────────────────────────────────────
        $genreMap = Genre::pluck('id', 'name')->all();

        // ── Build tag slug → id map (from normalized tags table) ─────────────
        $tagSlugToId = DB::table('tags')->pluck('id', 'slug')->all();

        DB::transaction(function () use ($data, $genreMap, $tagSlugToId) {

            // ── 1. Artists (users với role=artist) ───────────────────────────
            $this->command->getOutput()->write('   👤 Seeding artists... ');
            $artistRows = [];
            $now = now()->toDateTimeString();

            foreach ($data['artists'] as $a) {
                $artistRows[] = [
                    'id'                  => $a['id'],
                    'name'                => $a['name'],
                    'email'               => $a['email'],
                    'password'            => $a['password'],
                    'role'                => 'artist',
                    'status'              => 'Đang hoạt động',
                    'artist_verified_at'  => $a['artist_verified_at'] ?? $now,
                    'email_verified_at'   => $now,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }

            // insertOrIgnore: an toàn khi chạy nhiều lần
            foreach (array_chunk($artistRows, 100) as $chunk) {
                DB::table('users')->insertOrIgnore($chunk);
            }
            $this->command->getOutput()->writeln('<info>✅ ' . count($artistRows) . '</info>');

            // ── 2. Albums ────────────────────────────────────────────────────
            $this->command->getOutput()->write('   💿 Seeding albums... ');
            $albumRows = [];

            foreach ($data['albums'] as $a) {
                // Nếu user_id không tồn tại trong bảng users → dùng user_id đầu tiên của artists
                $userId = $a['user_id'] ?? ($data['artists'][0]['id'] ?? null);

                $albumRows[] = [
                    'id'           => $a['id'],
                    'user_id'      => $userId,
                    'title'        => $a['title'],
                    'description'  => $a['description'],
                    'cover_image'  => $a['cover_image'],
                    'released_date' => $a['released_date'],
                    'status'       => $a['status'],
                    'deleted'      => $a['deleted'] ? 1 : 0,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }

            foreach (array_chunk($albumRows, 100) as $chunk) {
                DB::table('albums')->insertOrIgnore($chunk);
            }
            $this->command->getOutput()->writeln('<info>✅ ' . count($albumRows) . '</info>');

            // ── 3. Songs ─────────────────────────────────────────────────────
            $this->command->getOutput()->write('   🎵 Seeding songs... ');
            $songRows  = [];
            $defaultUserId = $data['artists'][0]['id'] ?? null;

            foreach ($data['songs'] as $s) {
                $genreId = isset($s['genre_name']) ? ($genreMap[$s['genre_name']] ?? null) : null;
                $userId  = $s['user_id'] ?? $defaultUserId;

                $songRows[] = [
                    'id'           => $s['id'],
                    'user_id'      => $userId,
                    'genre_id'     => $genreId,
                    'album_id'     => $s['album_id'],
                    'title'        => mb_substr($s['title'], 0, 255),
                    'author'       => mb_substr($s['author'] ?? '', 0, 150),
                    'duration'     => $s['duration'],
                    'file_path'    => $s['file_path'],   // NULL cho Kaggle songs
                    'file_mime'    => $s['file_mime'],
                    'file_size'    => $s['file_size'],
                    'cover_image'  => $s['cover_image'],
                    'lyrics'       => $s['lyrics'],
                    'lyrics_type'  => $s['lyrics_type'],
                    'released_date' => $s['released_date'],
                    'is_vip'       => 0,
                    'status'       => $s['status'],
                    'listens'      => $s['listens'],
                    'deleted'      => $s['deleted'] ? 1 : 0,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }

            foreach (array_chunk($songRows, 200) as $chunk) {
                DB::table('songs')->insertOrIgnore($chunk);
            }
            $this->command->getOutput()->writeln('<info>✅ ' . count($songRows) . '</info>');

            // ── 4. Song features ───────────────────────────────────────────
            $this->command->getOutput()->write('   🎚️  Seeding song features... ');
            $songIds = collect($data['songs'])
                ->pluck('id')
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            if (!empty($songIds)) {
                DB::table('song_features')->whereIn('song_id', $songIds)->delete();
                DB::table('song_embeddings')->whereIn('song_id', $songIds)->delete();
            }

            $featureRows = [];

            foreach ($data['songs'] as $s) {
                $songId = (int) ($s['id'] ?? 0);
                if ($songId <= 0) {
                    continue;
                }

                $featurePayload = is_array($s['audio_features'] ?? null) ? $s['audio_features'] : [];
                $featureSource = (string) ($featurePayload['feature_source'] ?? $s['feature_source'] ?? 'spotify_kaggle');

                $featureRow = [
                    'song_id' => $songId,
                    'feature_source' => mb_substr($featureSource, 0, 50),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                foreach (self::FEATURE_COLUMNS as $column) {
                    $featureRow[$column] = $this->toNullableFloat($featurePayload[$column] ?? null);
                }

                $hasAnyFeatureValue = collect(self::FEATURE_COLUMNS)
                    ->contains(fn ($column) => $featureRow[$column] !== null);

                if ($hasAnyFeatureValue) {
                    $featureRows[] = $featureRow;
                }
            }

            foreach (array_chunk($featureRows, 300) as $chunk) {
                DB::table('song_features')->insert($chunk);
            }
            $this->command->getOutput()->writeln('<info>✅ ' . count($featureRows) . '</info>');

            // ── 5. Song embeddings ─────────────────────────────────────────
            $this->command->getOutput()->write('   🧠 Seeding song embeddings... ');
            $embeddingRows = [];
            $seenEmbeddingKeys = [];

            foreach ($data['songs'] as $s) {
                $songId = (int) ($s['id'] ?? 0);
                if ($songId <= 0) {
                    continue;
                }

                $featurePayload = is_array($s['audio_features'] ?? null) ? $s['audio_features'] : [];
                $embeddingItems = is_array($s['embeddings'] ?? null) ? $s['embeddings'] : [];

                if (empty($embeddingItems)) {
                    $fallbackVector = $this->buildFallbackEmbedding($featurePayload);
                    if (!empty($fallbackVector)) {
                        $embeddingItems[] = [
                            'embedding_type' => 'audio',
                            'vector' => $fallbackVector,
                            'dimension' => count($fallbackVector),
                            'model_version' => 'fallback-audio-v1',
                        ];
                    }
                }

                foreach ($embeddingItems as $item) {
                    $embeddingType = mb_substr((string) ($item['embedding_type'] ?? 'audio'), 0, 50);
                    $modelVersion = $item['model_version'] ?? 'spotify-kaggle-v1';

                    $vector = $item['vector'] ?? [];
                    if (!is_array($vector) || empty($vector)) {
                        continue;
                    }

                    $vector = array_values(array_map(
                        static fn ($value) => is_numeric($value) ? (float) $value : 0.0,
                        $vector
                    ));

                    $dimension = (int) ($item['dimension'] ?? count($vector));
                    if ($dimension <= 0 || $dimension !== count($vector)) {
                        $dimension = count($vector);
                    }

                    $dedupeKey = $songId . '|' . $embeddingType . '|' . (string) $modelVersion;
                    if (isset($seenEmbeddingKeys[$dedupeKey])) {
                        continue;
                    }
                    $seenEmbeddingKeys[$dedupeKey] = true;

                    $embeddingRows[] = [
                        'song_id' => $songId,
                        'embedding_type' => $embeddingType,
                        'vector' => json_encode($vector, JSON_UNESCAPED_UNICODE),
                        'dimension' => $dimension,
                        'model_version' => $modelVersion !== null ? mb_substr((string) $modelVersion, 0, 50) : null,
                        'created_at' => $now,
                    ];
                }
            }

            foreach (array_chunk($embeddingRows, 300) as $chunk) {
                DB::table('song_embeddings')->insert($chunk);
            }
            $this->command->getOutput()->writeln('<info>✅ ' . count($embeddingRows) . '</info>');

            // ── 6. Song tags (pivot) ───────────────────────────────────────
            $this->command->getOutput()->write('   🏷️  Seeding song tags... ');
            $songTagRows = [];
            $seenPairs   = [];

            foreach ($data['songs'] as $s) {
                if (empty($s['tags']) || !is_array($s['tags'])) continue;

                // Determine actual inserted song id (insertOrIgnore may reuse existing)
                // We match by the id provided in the seed data
                $songId = $s['id'];

                foreach (['mood', 'activity', 'topic'] as $type) {
                    foreach ($s['tags'][$type] ?? [] as $slug) {
                        $tagId = $tagSlugToId[$slug] ?? null;
                        if ($tagId === null) continue;

                        $pairKey = $songId . ':' . $tagId;
                        if (isset($seenPairs[$pairKey])) continue;
                        $seenPairs[$pairKey] = true;

                        $songTagRows[] = ['song_id' => $songId, 'tag_id' => $tagId];
                    }
                }
            }

            foreach (array_chunk($songTagRows, 500) as $chunk) {
                DB::table('song_tags')->insertOrIgnore($chunk);
            }
            $this->command->getOutput()->writeln('<info>✅ ' . count($songTagRows) . '</info>');
        });

        // ── Kết quả ───────────────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->table(
            ['Bảng', 'Số lượng'],
            [
                ['users (artists)',  DB::table('users')->where('role', 'artist')->count()],
                ['albums',           DB::table('albums')->count()],
                ['songs (tất cả)',   DB::table('songs')->count()],
                ['songs (có file)',  DB::table('songs')->whereNotNull('file_path')->count()],
                ['songs (metadata)', DB::table('songs')->whereNull('file_path')->count()],
                ['song_features',    DB::table('song_features')->count()],
                ['song_embeddings',  DB::table('song_embeddings')->count()],
            ]
        );

        $this->command->info('✅ Import Spotify dataset hoàn thành!');
        $this->command->line('   Mật khẩu tất cả artist seed: <comment>password</comment>');
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function buildFallbackEmbedding(array $featurePayload): array
    {
        $danceability = $this->toNullableFloat($featurePayload['danceability'] ?? null);
        $energy = $this->toNullableFloat($featurePayload['energy'] ?? null);
        $valence = $this->toNullableFloat($featurePayload['valence'] ?? null);
        $acousticness = $this->toNullableFloat($featurePayload['acousticness'] ?? null);
        $instrumentalness = $this->toNullableFloat($featurePayload['instrumentalness'] ?? null);
        $speechiness = $this->toNullableFloat($featurePayload['speechiness'] ?? null);
        $liveness = $this->toNullableFloat($featurePayload['liveness'] ?? null);
        $tempo = $this->toNullableFloat($featurePayload['tempo'] ?? null);
        $loudness = $this->toNullableFloat($featurePayload['loudness'] ?? null);

        $vector = [
            $danceability,
            $energy,
            $valence,
            $acousticness,
            $instrumentalness,
            $speechiness,
            $liveness,
            $tempo !== null ? min(max($tempo, 0.0), 250.0) / 250.0 : null,
            $loudness !== null ? min(max(($loudness + 60.0) / 60.0, 0.0), 1.0) : null,
        ];

        $vector = array_map(static fn ($value) => $value === null ? 0.0 : round((float) $value, 6), $vector);

        return collect($vector)->contains(fn ($value) => $value > 0) ? $vector : [];
    }
}
