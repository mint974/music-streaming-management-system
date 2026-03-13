<?php

namespace App\Console\Commands;

use App\Models\Song;
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

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info('Không có bài hát hẹn giờ cần xuất bản.');
            return self::SUCCESS;
        }

        $query->update([
            'status'     => 'published',
            'updated_at' => $now,
        ]);

        Log::info("PublishScheduledSongs: {$count} bài hát đã tự động xuất bản lúc {$now->toDateTimeString()} +07:00.");
        $this->info("Đã xuất bản tự động {$count} bài hát.");

        return self::SUCCESS;
    }
}
