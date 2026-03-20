<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
            MinhTanUserSeeder::class,
            GenreSeeder::class,
            ArtistPackageSeeder::class,
            ApprovedArtistSeeder::class,
            SpotifyDatasetSeeder::class,
            CustomSongsSeeder::class,
            UpdateSongLyricsSeeder::class,
        ]);
    }
}
