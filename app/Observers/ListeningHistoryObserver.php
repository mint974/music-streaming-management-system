<?php

namespace App\Observers;

use App\Models\ListeningHistory;
use App\Jobs\SyncUserTasteProfileJob;

class ListeningHistoryObserver
{
    public function created(ListeningHistory $history): void
    {
        $this->dispatchSyncJob($history);
    }

    public function updated(ListeningHistory $history): void
    {
        $this->dispatchSyncJob($history);
    }

    private function dispatchSyncJob(ListeningHistory $history)
    {
        if (!$history->user_id) return; // Khách Guest thì bỏ qua thuật toán AI
        
        SyncUserTasteProfileJob::dispatch(
            $history->user_id,
            $history->song_id,
            $history->listen_count ?? 1,
            $history->played_percent ?? 50
        );
    }
}
