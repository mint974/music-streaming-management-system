<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Song;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SpotifyDatasetSeeder extends Seeder
{
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

        DB::transaction(function () use ($data, $genreMap) {

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
                    'tags'         => json_encode($s['tags']),
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
            ]
        );

        $this->command->info('✅ Import Spotify dataset hoàn thành!');
        $this->command->line('   Mật khẩu tất cả artist seed: <comment>password</comment>');
    }
}
