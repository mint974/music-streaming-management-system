<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncUserTasteProfileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;
    public $songId;
    public $listenCount;
    public $playedPercent;

    public function __construct($userId, $songId, $listenCount, $playedPercent)
    {
        $this->userId = $userId;
        $this->songId = $songId;
        $this->listenCount = $listenCount;
        $this->playedPercent = $playedPercent;
    }

    public function handle(): void
    {
        try {
            $response = Http::timeout(5)->post('http://127.0.0.1:5000/api/users/update-profile', [
                'user_id' => $this->userId,
                'song_id' => $this->songId,
                'listen_count' => $this->listenCount,
                'played_percent' => $this->playedPercent,
            ]);

            if (!$response->successful()) {
                Log::warning('AI Server failed to update user profile', ['response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('AI Server Connection Error (SyncUserTasteProfileJob)', ['error' => $e->getMessage()]);
        }
    }
}
