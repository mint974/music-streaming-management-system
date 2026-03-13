<?php

namespace Tests\Feature;

use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublishScheduledSongsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_publishes_due_scheduled_songs_only(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-13 10:00:00', config('app.timezone')));

        $artist = User::factory()->artist()->create();
        $now = now();

        $dueSong = Song::create([
            'user_id' => $artist->id,
            'title' => 'Due Song',
            'lyrics_type' => 'plain',
            'status' => 'scheduled',
            'publish_at' => $now->copy()->subMinute(),
        ]);

        $futureSong = Song::create([
            'user_id' => $artist->id,
            'title' => 'Future Song',
            'lyrics_type' => 'plain',
            'status' => 'scheduled',
            'publish_at' => $now->copy()->addMinutes(5),
        ]);

        $draftSong = Song::create([
            'user_id' => $artist->id,
            'title' => 'Draft Song',
            'lyrics_type' => 'plain',
            'status' => 'draft',
            'publish_at' => $now->copy()->subMinute(),
        ]);

        $this->artisan('songs:publish-scheduled')
            ->expectsOutput('Đã xuất bản tự động 1 bài hát.')
            ->assertSuccessful();

        $this->assertSame('published', $dueSong->fresh()->status);
        $this->assertSame('scheduled', $futureSong->fresh()->status);
        $this->assertSame('draft', $draftSong->fresh()->status);

        Carbon::setTestNow();
    }

    public function test_it_shows_message_when_no_due_songs(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-13 10:00:00', config('app.timezone')));

        $artist = User::factory()->artist()->create();

        Song::create([
            'user_id' => $artist->id,
            'title' => 'Still Scheduled',
            'lyrics_type' => 'plain',
            'status' => 'scheduled',
            'publish_at' => now()->addMinute(),
        ]);

        $this->artisan('songs:publish-scheduled')
            ->expectsOutput('Không có bài hát hẹn giờ cần xuất bản.')
            ->assertSuccessful();

        Carbon::setTestNow();
    }
}
