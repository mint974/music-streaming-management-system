<?php

namespace App\Console\Commands;

use App\Models\ArtistRegistration;
use App\Models\Subscription;
use App\Notifications\MembershipExpiredNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptions extends Command
{
    protected $signature   = 'subscription:expire';
    protected $description = 'Đánh dấu hết hạn các subscription / gói nghệ sĩ quá hạn và gửi email thông báo';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        $premiumCount = $this->expirePremiumSubscriptions($today);
        $artistCount  = $this->expireArtistPackages($today);

        $this->info("Đã xử lý: {$premiumCount} gói Premium, {$artistCount} gói Nghệ sĩ hết hạn.");
        return self::SUCCESS;
    }

    // ── Premium subscriptions ────────────────────────────────────────────────

    private function expirePremiumSubscriptions(string $today): int
    {
        $expiredSubs = Subscription::with('user', 'vip')
            ->where('status', 'active')
            ->where('end_date', '<', $today)
            ->get();

        if ($expiredSubs->isEmpty()) {
            return 0;
        }

        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($expiredSubs as $sub) {
                $sub->update(['status' => 'expired']);
                $count++;

                $user = $sub->user;
                if (! $user) {
                    continue;
                }

                $hasOtherActive = Subscription::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->where('end_date', '>=', $today)
                    ->exists();

                if (! $hasOtherActive) {
                    $user->removeRole('premium');
                    if (! $user->hasRole('admin') && ! $user->hasRole('artist')) {
                        $user->assignRole('free');
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ExpireSubscriptions (premium) error: ' . $e->getMessage());
            $this->error('Lỗi xử lý Premium: ' . $e->getMessage());
            return 0;
        }

        // Gửi email hết hạn (ngoài transaction DB để tránh rollback khi lỗi mail)
        foreach ($expiredSubs as $sub) {
            if (!$sub->user) continue;
            try {
                $sub->user->notify(new MembershipExpiredNotification(
                    'Premium',
                    $sub->vip?->title ?? 'Gói Premium'
                ));
            } catch (\Throwable $e) {
                Log::warning("ExpireSubscriptions: Failed expiry email for Premium user #{$sub->user_id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    // ── Artist packages ──────────────────────────────────────────────────────

    private function expireArtistPackages(string $today): int
    {
        $expiredRegs = ArtistRegistration::with('user', 'package')
            ->where('status', 'approved')
            ->where('expires_at', '<', $today)
            ->get();

        if ($expiredRegs->isEmpty()) {
            return 0;
        }

        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($expiredRegs as $reg) {
                $reg->update(['status' => 'expired']);
                $count++;

                $user = $reg->user;
                if (!$user) continue;

                // Thu hồi role artist khi gói hết hạn (không đụng role premium/admin nếu có).
                if ($user->isArtist()) {
                    $user->removeRole('artist');
                    if (! $user->hasRole('admin') && ! $user->hasRole('premium')) {
                        $user->assignRole('free');
                    }
                    Log::info("ExpireSubscriptions: user #{$user->id} ({$user->email}) Artist package expired, artist role removed.");
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ExpireSubscriptions (artist) error: ' . $e->getMessage());
            $this->error('Lỗi xử lý Nghệ sĩ: ' . $e->getMessage());
            return 0;
        }

        // Gửi email hết hạn
        foreach ($expiredRegs as $reg) {
            if (!$reg->user) continue;
            try {
                $reg->user->notify(new MembershipExpiredNotification(
                    'Nghệ sĩ',
                    $reg->package?->name ?? 'Gói Nghệ sĩ'
                ));
            } catch (\Throwable $e) {
                Log::warning("ExpireSubscriptions: Failed expiry email for Artist user #{$reg->user_id}: " . $e->getMessage());
            }
        }

        return $count;
    }
}
