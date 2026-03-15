<?php

namespace Database\Seeders;

use App\Models\AccountHistory;
use App\Models\ArtistPackage;
use App\Models\ArtistRegistration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class ApprovedArtistSeeder extends Seeder
{
    /**
     * Seed 1 artist account with full approval lifecycle:
     * account created -> artist registration submitted -> payment done -> admin approved.
     */
    public function run(): void
    {
        $now = now();

        $admin = User::query()
            ->where('role', 'admin')
            ->where('deleted', false)
            ->orderBy('id')
            ->first();

        $artist = User::updateOrCreate(
            ['email' => 'artist.seed@bluewavemusic.com'],
            [
                'name' => 'Huy Hoang',
                'password' => Hash::make('Az@12345'),
                'phone' => '0988001122',
                'birthday' => '2000-10-15',
                'gender' => 'Nam',
                'role' => 'artist',
                'status' => 'Đang hoạt động',
                'deleted' => false,
                'email_verified_at' => $now,
                'artist_verified_at' => $now->copy()->subDays(4),
                'artist_revoked_at' => null,
                'artist_name' => 'HH Beats',
                'bio' => 'Independent artist seeded for full artist registration lifecycle.',
                'avatar' => '/storage/avt.jpg',
                'cover_image' => null,
            ]
        );

        $package = ArtistPackage::query()->where('is_active', true)->orderByDesc('price')->first();
        if (! $package) {
            $package = ArtistPackage::create([
                'name' => 'Goi Seed Artist',
                'description' => 'Seed package for artist lifecycle.',
                'price' => 249000,
                'duration_days' => 365,
                'is_active' => true,
            ]);
        }

        $paidAt = Carbon::now('Asia/Ho_Chi_Minh')->subDays(6);
        $reviewedAt = Carbon::now('Asia/Ho_Chi_Minh')->subDays(4);

        ArtistRegistration::updateOrCreate(
            ['transaction_code' => 'ART-SEED-APPROVED-001'],
            [
                'user_id' => $artist->id,
                'package_id' => $package->id,
                'artist_name' => $artist->artist_name ?? $artist->name,
                'bio' => $artist->bio,
                'status' => 'approved',
                'amount_paid' => (int) $package->price,
                'transaction_code' => 'ART-SEED-APPROVED-001',
                'vnp_transaction_no' => 'VNPSEED000001',
                'vnp_pay_date' => $paidAt->format('YmdHis'),
                'paid_at' => $paidAt,
                'refund_amount' => null,
                'refunded_at' => null,
                'refund_status' => null,
                'refund_confirmed_by' => null,
                'refund_confirmed_at' => null,
                'admin_note' => 'Hồ sơ hợp lệ, duyệt tài khoản nghệ sĩ seed.',
                'reviewed_by' => $admin?->id,
                'reviewed_at' => $reviewedAt,
                'expires_at' => $reviewedAt->copy()->addDays((int) ($package->duration_days ?? 365)),
            ]
        );

        AccountHistory::query()
            ->where('user_id', $artist->id)
            ->whereIn('action', [
                'Đăng ký tài khoản mới',
                'Gửi đăng ký trở thành Nghệ sĩ',
                'Thanh toán gói đăng ký Nghệ sĩ thành công',
                '[Admin] Phê duyệt đăng ký Nghệ sĩ — ' . ($artist->artist_name ?? $artist->name),
                '[Hệ thống] Nâng cấp vai trò tài khoản thành Nghệ sĩ',
            ])
            ->delete();

        $historyRows = [
            [
                'action' => 'Đăng ký tài khoản mới',
                'created_by' => $artist->id,
                'created_at' => $paidAt->copy()->subDays(2),
            ],
            [
                'action' => 'Gửi đăng ký trở thành Nghệ sĩ',
                'created_by' => $artist->id,
                'created_at' => $paidAt->copy()->subHours(3),
            ],
            [
                'action' => 'Thanh toán gói đăng ký Nghệ sĩ thành công',
                'created_by' => $artist->id,
                'created_at' => $paidAt,
            ],
            [
                'action' => '[Admin] Phê duyệt đăng ký Nghệ sĩ — ' . ($artist->artist_name ?? $artist->name),
                'created_by' => $admin?->id ?? $artist->id,
                'created_at' => $reviewedAt,
            ],
            [
                'action' => '[Hệ thống] Nâng cấp vai trò tài khoản thành Nghệ sĩ',
                'created_by' => $admin?->id ?? $artist->id,
                'created_at' => $reviewedAt->copy()->addMinute(),
            ],
        ];

        foreach ($historyRows as $row) {
            AccountHistory::create([
                'type' => 'history',
                'action' => $row['action'],
                'status' => 'Đang hoạt động',
                'lock_reason' => null,
                'content' => null,
                'unlock_status' => null,
                'admin_note' => null,
                'handled_by' => null,
                'handled_at' => null,
                'user_id' => $artist->id,
                'created_by' => $row['created_by'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['created_at'],
            ]);
        }

        $this->command->info('ApprovedArtistSeeder: seeded artist account artist.seed@bluewavemusic.com | password: Aa@12345');
    }
}
