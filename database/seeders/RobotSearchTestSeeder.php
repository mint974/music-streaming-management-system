<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\ArtistPackage;
use App\Models\ArtistProfile;
use App\Models\Genre;
use App\Models\Role;
use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class RobotSearchTestSeeder extends Seeder
{
    public function run(): void
    {
        $database = DB::connection()->getDatabaseName();

        if (app()->environment() !== 'testing' || $database !== 'wavex_music_testing') {
            throw new RuntimeException('RobotSearchTestSeeder may only run against wavex_music_testing.');
        }

        DB::transaction(function (): void {
            $role = Role::query()->firstOrCreate(
                ['slug' => 'artist'],
                ['name' => 'Nghệ sĩ', 'description' => 'Tài khoản nghệ sĩ']
            );

            $artist = User::query()->updateOrCreate(
                ['email' => 'robot.search.artist@example.test'],
                [
                    'name' => 'Robot Search Artist',
                    'password' => Hash::make(Str::random(40)),
                    'status' => 'Đang hoạt động',
                    'deleted' => false,
                    'is_onboarded' => true,
                    'email_verified_at' => now(),
                ]
            );
            $artist->syncRoles([$role->slug]);

            $package = ArtistPackage::query()->firstOrCreate(
                ['name' => 'Robot Search Test Package'],
                ['description' => 'Testing-only artist package', 'price' => 0, 'duration_days' => 365, 'is_active' => true]
            );

            $profile = ArtistProfile::query()->updateOrCreate(
                ['user_id' => $artist->id],
                [
                    'artist_package_id' => $package->id,
                    'stage_name' => 'Robot Search Artist',
                    'bio' => 'Deterministic artist for Robot search automation.',
                    'status' => ArtistProfile::STATUS_ACTIVE,
                    'verified_at' => now(),
                    'start_date' => now(),
                    'end_date' => now()->addYear(),
                ]
            );

            $genre = Genre::query()->updateOrCreate(
                ['slug' => 'robot-search-genre'],
                ['name' => 'Robot Search Genre', 'description' => 'Testing-only genre', 'is_active' => true]
            );

            $album = Album::query()->updateOrCreate(
                ['artist_profile_id' => $profile->id, 'title' => 'Robot Search Album'],
                [
                    'description' => 'Deterministic album for Robot search automation.',
                    'released_date' => '2026-01-15',
                    'status' => 'published',
                    'deleted' => false,
                ]
            );

            foreach ([
                ['title' => 'Robot Search Song Alpha', 'duration' => 185, 'listens' => 120],
                ['title' => 'Robot Search Song Beta', 'duration' => 205, 'listens' => 80],
            ] as $songData) {
                Song::query()->updateOrCreate(
                    ['artist_profile_id' => $profile->id, 'title' => $songData['title']],
                    [
                        'genre_id' => $genre->id,
                        'album_id' => $album->id,
                        'duration' => $songData['duration'],
                        'file_path' => 'robot-search/' . Str::slug($songData['title']) . '.mp3',
                        'file_mime' => 'audio/mpeg',
                        'file_size' => 1,
                        'released_date' => '2026-01-15',
                        'is_vip' => false,
                        'status' => 'published',
                        'listens' => $songData['listens'],
                        'deleted' => false,
                    ]
                );
            }
        });

        $this->command?->info("Robot search data prepared in {$database}.");
    }
}
