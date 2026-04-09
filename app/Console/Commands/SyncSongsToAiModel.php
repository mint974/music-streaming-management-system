<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Song;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncSongsToAiModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:sync-songs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all published songs to the Hum-to-Search AI model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync to AI Model...');
        
        $songs = Song::where('status', 'published')->whereNotNull('file_path')->get();
        $total = $songs->count();
        $this->info("Found {$total} published songs to sync.");

        $success = 0;
        $failed = 0;

        foreach ($songs as $song) {
            $this->info("Syncing song: {$song->title} (ID: {$song->id})");
            
            try {
                $response = Http::post('http://127.0.0.1:8000/update-model?song_path=' . urlencode($song->file_path));

                if ($response->successful()) {
                    $this->line(" - OK: {$song->title}");
                    $success++;
                } else {
                    $this->error(" - FAILED: {$song->title} | Reason: " . $response->body());
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error(" - ERROR: {$song->title} | Reason: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("Sync completed! Success: {$success}, Failed: {$failed}");
    }
}
