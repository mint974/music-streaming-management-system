<?php

namespace App\Console\Commands;

use App\Models\Song;
use App\Services\ReleaseNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledSongs extends Command
{
    protected $signature = 'songs:publish-scheduled';
    protected $description = 'Tự động chuyển bài hát hẹn giờ sang đã xuất bản khi đến thời điểm publish_at';

    public function handle(): int
    {
        // Use explicit Asia/Ho_Chi_Minh so the comparison is correct
        // regardless of system timezone or queue worker startup time.
        $now = now(config('app.timezone'));

        $query = Song::query()
            ->where('deleted', false)
            ->where('status', 'scheduled')
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', $now);

        $songs = (clone $query)
            ->with('artist')
            ->get();

        $count = $songs->count();

        if ($count === 0) {
            $this->info('Không có bài hát hẹn giờ cần xuất bản.');
            return self::SUCCESS;
        }

        $notified = 0;

        foreach ($songs as $song) {
            $song->forceFill([
                'status'     => 'published',
                'updated_at' => $now,
            ])->save();

            if ($song->artist) {
                ReleaseNotificationService::notifyFollowers($song->artist, $song);
                $notified++;
            }
        }

        Log::info("PublishScheduledSongs: {$count} bài hát đã tự động xuất bản lúc {$now->toDateTimeString()} +07:00. Sent notifications for {$notified} songs.");
        $this->info("Đã xuất bản tự động {$count} bài hát và gửi thông báo cho {$notified} bài.");

        return self::SUCCESS;
    }
}
