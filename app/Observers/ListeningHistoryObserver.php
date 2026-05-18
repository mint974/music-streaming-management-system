<?php

namespace App\Observers;

use App\Models\ListeningHistory;
use App\Jobs\SyncUserTasteProfileJob;

class ListeningHistoryObserver
{
    public function created(ListeningHistory $history): void
    {
        // Bản ghi mới → luôn sync để cập nhật profile
        $this->dispatchSyncJob($history);
    }

    public function updated(ListeningHistory $history): void
    {
        // Chỉ sync lại khi played_percent tăng đáng kể (>=10%) để tránh
        // dispatch job liên tục mỗi 15 giây từ flushProgress()
        $oldPercent = (float) ($history->getOriginal('played_percent') ?? 0);
        $newPercent = (float) ($history->played_percent ?? 0);

        if (($newPercent - $oldPercent) >= 10.0 || $history->wasChanged('is_completed')) {
            $this->dispatchSyncJob($history);
        }
    }

    private function dispatchSyncJob(ListeningHistory $history)
    {
        if (!$history->user_id) return;

        SyncUserTasteProfileJob::dispatch(
            $history->user_id,
            $history->song_id,
            $history->listen_count ?? 1,
            $history->played_percent ?? 50
        )->delay(now()->addSeconds(5)); // Đợi 5 giây để tránh race condition với flush tiếp theo
    }
}
