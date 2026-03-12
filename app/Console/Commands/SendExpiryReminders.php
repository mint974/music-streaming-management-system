<?php

namespace App\Console\Commands;

use App\Mail\ArtistPackageExpiringSoon;
use App\Mail\SubscriptionExpiringSoon;
use App\Models\ArtistRegistration;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendExpiryReminders extends Command
{
    protected $signature   = 'subscription:remind';
    protected $description = 'Gửi email nhắc nhở trước 1 ngày khi gói Premium hoặc gói Nghệ sĩ sắp hết hạn';

    public function handle(): int
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        $premiumCount = 0;
        $artistCount  = 0;

        // ── 1. Gói Premium hết hạn vào ngày mai ──────────────────────────────
        $subs = Subscription::with('user', 'vip')
            ->where('status', 'active')
            ->whereDate('end_date', $tomorrow)
            ->get();

        foreach ($subs as $sub) {
            if (!$sub->user) continue;

            try {
                Mail::to($sub->user->email)->send(new SubscriptionExpiringSoon($sub));
                $premiumCount++;
                Log::info("ExpiryReminder: Premium reminder sent to user #{$sub->user_id} ({$sub->user->email}), expires {$tomorrow}.");
            } catch (\Throwable $e) {
                Log::warning("ExpiryReminder: Failed Premium reminder for user #{$sub->user_id}: " . $e->getMessage());
            }
        }

        // ── 2. Gói Nghệ sĩ hết hạn vào ngày mai ─────────────────────────────
        $registrations = ArtistRegistration::with('user', 'package')
            ->where('status', 'approved')
            ->whereDate('expires_at', $tomorrow)
            ->get();

        foreach ($registrations as $reg) {
            if (!$reg->user) continue;

            try {
                Mail::to($reg->user->email)->send(new ArtistPackageExpiringSoon($reg));
                $artistCount++;
                Log::info("ExpiryReminder: Artist reminder sent to user #{$reg->user_id} ({$reg->user->email}), expires {$tomorrow}.");
            } catch (\Throwable $e) {
                Log::warning("ExpiryReminder: Failed Artist reminder for user #{$reg->user_id}: " . $e->getMessage());
            }
        }

        $this->info("Đã gửi nhắc nhở: {$premiumCount} Premium, {$artistCount} Nghệ sĩ.");
        return self::SUCCESS;
    }
}
