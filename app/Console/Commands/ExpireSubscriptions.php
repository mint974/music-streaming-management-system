<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptions extends Command
{
    protected $signature   = 'subscription:expire';
    protected $description = 'Đánh dấu hết hạn các subscription quá end_date và hạ role user về free';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        $expiredSubs = Subscription::with('user')
            ->where('status', 'active')
            ->where('end_date', '<', $today)
            ->get();

        if ($expiredSubs->isEmpty()) {
            $this->info('Không có subscription nào hết hạn hôm nay.');
            return self::SUCCESS;
        }

        DB::beginTransaction();
        try {
            $count = 0;

            foreach ($expiredSubs as $sub) {
                $sub->update(['status' => 'expired']);
                $count++;

                // Hạ role về free nếu user không còn subscription active nào còn hạn
                $user = $sub->user;
                if ($user && $user->isPremium()) {
                    $hasOtherActive = Subscription::where('user_id', $user->id)
                        ->where('status', 'active')
                        ->where('end_date', '>=', $today)
                        ->exists();

                    if (! $hasOtherActive) {
                        $user->update(['role' => 'free']);
                        Log::info("ExpireSubscriptions: user #{$user->id} ({$user->email}) downgraded to free.");
                    }
                }
            }

            DB::commit();
            $this->info("Đã xử lý {$count} subscription(s) hết hạn.");
            return self::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ExpireSubscriptions error: ' . $e->getMessage());
            $this->error('Lỗi: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
