<?php

namespace App\Console\Commands;

use App\Mail\ArtistPackageExpired;
use App\Mail\SubscriptionExpired;
use App\Models\ArtistRegistration;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ExpireSubscriptions extends Command
{
    protected $signature   = 'subscription:expire';
    protected $description = 'Đánh dấu hết hạn các subscription / gói nghệ sĩ quá hạn, hạ role về free và gửi email thông báo';

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
                if (!$user) continue;

                // Hạ role về free nếu không còn subscription active nào
                if ($user->isPremium()) {
                    $hasOtherActive = Subscription::where('user_id', $user->id)
                        ->where('status', 'active')
                        ->where('end_date', '>=', $today)
                        ->exists();

                    if (!$hasOtherActive) {
                        $user->update(['role' => 'free']);
                        Log::info("ExpireSubscriptions: user #{$user->id} ({$user->email}) Premium downgraded to free.");
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
                Mail::to($sub->user->email)->send(new SubscriptionExpired($sub));
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

                // Hạ role về free (bài hát/album không bị xóa)
                if ($user->isArtist()) {
                    $user->update(['role' => 'free']);
                    Log::info("ExpireSubscriptions: user #{$user->id} ({$user->email}) Artist package expired, role → free.");
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
                Mail::to($reg->user->email)->send(new ArtistPackageExpired($reg));
            } catch (\Throwable $e) {
                Log::warning("ExpireSubscriptions: Failed expiry email for Artist user #{$reg->user_id}: " . $e->getMessage());
            }
        }

        return $count;
    }
}
