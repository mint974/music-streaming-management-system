<?php

namespace Tests\Feature;

use App\Models\ArtistPackage;
use App\Models\ArtistProfile;
use App\Models\Genre;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublishScheduledSongsCommandTest extends TestCase
{
    use RefreshDatabase;

    private function createSongOwner(User $artist): array
    {
        $package = ArtistPackage::query()->create([
            'name' => 'Scheduled Song Test Package',
            'description' => 'Package fixture for scheduled-song command tests.',
            'price' => 100000,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $profile = ArtistProfile::query()->create([
            'user_id' => $artist->id,
            'artist_package_id' => $package->id,
            'stage_name' => 'Scheduled Song Artist',
            'status' => ArtistProfile::STATUS_ACTIVE,
        ]);

        $genre = Genre::query()->create(['name' => 'Scheduled Song Test Genre']);

        return [$profile, $genre];
    }

    public function test_it_publishes_due_scheduled_songs_only(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-13 10:00:00', config('app.timezone')));

        $artist = User::factory()->artist()->create();
        [$profile, $genre] = $this->createSongOwner($artist);
        $now = now();

        $dueSong = Song::create([
            'artist_profile_id' => $profile->id,
            'genre_id' => $genre->id,
            'title' => 'Due Song',
            'status' => 'scheduled',
            'publish_at' => $now->copy()->subMinute(),
        ]);

        $futureSong = Song::create([
            'artist_profile_id' => $profile->id,
            'genre_id' => $genre->id,
            'title' => 'Future Song',
            'status' => 'scheduled',
            'publish_at' => $now->copy()->addMinutes(5),
        ]);

        $draftSong = Song::create([
            'artist_profile_id' => $profile->id,
            'genre_id' => $genre->id,
            'title' => 'Draft Song',
            'status' => 'draft',
            'publish_at' => $now->copy()->subMinute(),
        ]);

        $this->artisan('songs:publish-scheduled')
            ->expectsOutputToContain('Đã xuất bản tự động 1 bài hát')
            ->assertSuccessful();

        $this->assertSame('published', $dueSong->fresh()->status);
        $this->assertSame('scheduled', $futureSong->fresh()->status);
        $this->assertSame('draft', $draftSong->fresh()->status);

    }

    public function test_it_shows_message_when_no_due_songs(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-13 10:00:00', config('app.timezone')));

        $artist = User::factory()->artist()->create();
        [$profile, $genre] = $this->createSongOwner($artist);

        Song::create([
            'artist_profile_id' => $profile->id,
            'genre_id' => $genre->id,
            'title' => 'Still Scheduled',
            'status' => 'scheduled',
            'publish_at' => now()->addMinute(),
        ]);

        $this->artisan('songs:publish-scheduled')
            ->expectsOutput('Không có bài hát hẹn giờ cần xuất bản.')
            ->assertSuccessful();

    }
}
