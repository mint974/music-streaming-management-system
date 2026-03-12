<?php

namespace Database\Seeders;

use App\Models\ArtistPackage;
use App\Models\ArtistRegistration;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Vip;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder test chức năng nhắc nhở & hết hạn gói Premium + Nghệ sĩ.
 *
 * Tạo ra các tài khoản và gói với end_date / expires_at ở các trạng thái:
 *
 *  ┌─────────────────────────────────────────────────────────────────────┐
 *  │ Email                              │ Loại gói  │ Trạng thái         │
 *  ├─────────────────────────────────────────────────────────────────────┤
 *  │ expire.premium.tomorrow@test.com   │ Premium   │ Hết hạn ngày mai   │
 *  │ expire.premium.today@test.com      │ Premium   │ Hết hạn hôm nay    │
 *  │ expire.premium.past@test.com       │ Premium   │ Đã hết hạn (-1 n)  │
 *  │ expire.premium.active@test.com     │ Premium   │ Còn hạn (30 ngày)  │
 *  │ expire.artist.tomorrow@test.com    │ Nghệ sĩ   │ Hết hạn ngày mai   │
 *  │ expire.artist.today@test.com       │ Nghệ sĩ   │ Hết hạn hôm nay    │
 *  │ expire.artist.past@test.com        │ Nghệ sĩ   │ Đã hết hạn (-1 n)  │
 *  │ expire.artist.active@test.com      │ Nghệ sĩ   │ Còn hạn (30 ngày)  │
 *  └─────────────────────────────────────────────────────────────────────┘
 *
 * Tất cả mật khẩu: Test@12345
 *
 * Cách test:
 *   php artisan db:seed --class=ExpiryTestSeeder
 *   php artisan subscription:remind    ← gửi mail nhắc (tomorrow cases)
 *   php artisan subscription:expire    ← xử lý hết hạn (today/past cases)
 */
class ExpiryTestSeeder extends Seeder
{
    private const PASSWORD = 'Test@12345';

    public function run(): void
    {
        $monthly = Vip::find('monthly');
        $package = ArtistPackage::where('is_active', true)->first();

        if (!$monthly) {
            $this->command->error('Vip "monthly" không tìm thấy. Chạy migrate trước.');
            return;
        }
        if (!$package) {
            $this->command->error('Không có ArtistPackage nào. Chạy ArtistPackageSeeder trước.');
            return;
        }

        // ── Premium subscriptions ────────────────────────────────────────────

        $this->seedPremiumUser(
            email:    'expire.premium.tomorrow@test.com',
            name:     '[Test] Premium – Hết hạn ngày mai',
            endDate:  Carbon::tomorrow(),
            vip:      $monthly,
            label:    'hết hạn NGÀY MAI (nhắc nhở)'
        );

        $this->seedPremiumUser(
            email:    'expire.premium.today@test.com',
            name:     '[Test] Premium – Hết hạn hôm nay',
            endDate:  Carbon::today(),
            vip:      $monthly,
            label:    'hết hạn HÔM NAY (expire job)'
        );

        $this->seedPremiumUser(
            email:    'expire.premium.past@test.com',
            name:     '[Test] Premium – Đã hết hạn hôm qua',
            endDate:  Carbon::yesterday(),
            vip:      $monthly,
            label:    'đã hết hạn HÔM QUA (expire job – còn sót)'
        );

        $this->seedPremiumUser(
            email:    'expire.premium.active@test.com',
            name:     '[Test] Premium – Còn 30 ngày',
            endDate:  Carbon::today()->addDays(30),
            vip:      $monthly,
            label:    'còn 30 ngày (không bị ảnh hưởng)'
        );

        // ── Artist packages ──────────────────────────────────────────────────

        $this->seedArtistUser(
            email:    'expire.artist.tomorrow@test.com',
            name:     '[Test] Artist – Hết hạn ngày mai',
            expiresAt: Carbon::tomorrow(),
            package:  $package,
            label:    'hết hạn NGÀY MAI (nhắc nhở)'
        );

        $this->seedArtistUser(
            email:    'expire.artist.today@test.com',
            name:     '[Test] Artist – Hết hạn hôm nay',
            expiresAt: Carbon::today(),
            package:  $package,
            label:    'hết hạn HÔM NAY (expire job)'
        );

        $this->seedArtistUser(
            email:    'expire.artist.past@test.com',
            name:     '[Test] Artist – Đã hết hạn hôm qua',
            expiresAt: Carbon::yesterday(),
            package:  $package,
            label:    'đã hết hạn HÔM QUA (expire job – còn sót)'
        );

        $this->seedArtistUser(
            email:    'expire.artist.active@test.com',
            name:     '[Test] Artist – Còn 30 ngày',
            expiresAt: Carbon::today()->addDays(30),
            package:  $package,
            label:    'còn 30 ngày (không bị ảnh hưởng)'
        );

        $this->command->newLine();
        $this->command->info('✅ ExpiryTestSeeder hoàn tất. Mật khẩu: ' . self::PASSWORD);
        $this->command->newLine();
        $this->command->line('  Lệnh test:');
        $this->command->line('    php artisan subscription:remind   ← gửi mail nhắc (.tomorrow)');
        $this->command->line('    php artisan subscription:expire   ← xử lý hết hạn (.today / .past)');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function seedPremiumUser(
        string $email,
        string $name,
        Carbon $endDate,
        Vip    $vip,
        string $label
    ): void {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'password'          => Hash::make(self::PASSWORD),
                'role'              => 'premium',
                'status'            => 'Đang hoạt động',
                'deleted'           => false,
                'email_verified_at' => now(),
            ]
        );

        // Xóa subscription cũ của user này (idempotent)
        Subscription::where('user_id', $user->id)->delete();

        $startDate = $endDate->copy()->subDays($vip->duration_days);

        $sub = Subscription::create([
            'user_id'     => $user->id,
            'vip_id'      => $vip->id,
            'start_date'  => $startDate->toDateString(),
            'end_date'    => $endDate->toDateString(),
            'status'      => 'active',
            'amount_paid' => $vip->price,
        ]);

        Payment::create([
            'subscription_id'  => $sub->id,
            'method'           => 'VNPAY',
            'status'           => 'paid',
            'transaction_code' => 'TEST_SUB_' . $sub->id . '_' . time(),
            'date'             => $startDate,
        ]);

        $this->command->line("  ✓ [Premium] {$email} — {$label} ({$endDate->format('d/m/Y')})");
    }

    private function seedArtistUser(
        string         $email,
        string         $name,
        Carbon         $expiresAt,
        ArtistPackage  $package,
        string         $label
    ): void {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'artist_name'       => $name,
                'password'          => Hash::make(self::PASSWORD),
                'role'              => 'artist',
                'status'            => 'Đang hoạt động',
                'deleted'           => false,
                'email_verified_at' => now(),
                'artist_verified_at'=> now(),
            ]
        );

        // Xóa đăng ký nghệ sĩ cũ của user này (idempotent)
        ArtistRegistration::where('user_id', $user->id)->delete();

        $paidAt = $expiresAt->copy()->subDays($package->duration_days);

        ArtistRegistration::create([
            'user_id'          => $user->id,
            'package_id'       => $package->id,
            'artist_name'      => $name,
            'bio'              => 'Tài khoản test — ' . $label,
            'status'           => 'approved',
            'amount_paid'      => $package->price,
            'transaction_code' => 'TEST_ART_' . $user->id . '_' . time(),
            'paid_at'          => $paidAt,
            'reviewed_at'      => $paidAt,
            'expires_at'       => $expiresAt,
        ]);

        $this->command->line("  ✓ [Artist]  {$email} — {$label} ({$expiresAt->format('d/m/Y')})");
    }
}
