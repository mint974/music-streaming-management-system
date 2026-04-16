<?php

namespace Database\Seeders;

use App\Models\ArtistPackage;
use App\Models\ArtistRegistration;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MinhTanArtistRegistrationSeeder extends Seeder
{
    /**
     * Giả lập lịch sử đăng ký gói Nghệ sĩ cho Minh Tân.
     *
     * Mỗi gói có duration_days = 365 → mỗi lần đăng ký cách nhau ~1 năm.
     * Tất cả hết hạn TRƯỚC ngày hiện tại (16/04/2026) để user tự demo đăng ký mới.
     *
     *  ┌──────────────┬───────────────────────┬─────────────┬────────────────┐
     *  │ Lần          │ Gói                   │ Duyệt       │ Hết hạn        │
     *  ├──────────────┼───────────────────────┼─────────────┼────────────────┤
     *  │ T12/2022     │ Khởi đầu → TỪ CHỐI   │ —           │ —              │
     *  │ T03/2023     │ Khởi đầu              │ 10/03/2023  │ 10/03/2024     │
     *  │ T03/2024     │ Tiêu chuẩn            │ 12/03/2024  │ 12/03/2025     │
     *  │ T03/2025     │ Chuyên nghiệp         │ 10/03/2025  │ 10/03/2026     │
     *  └──────────────┴───────────────────────┴─────────────┴────────────────┘
     *  Lần cuối kết thúc 10/03/2026 < 16/04/2026 → tài khoản free sau seed.
     */
    public function run(): void
    {
        $user = User::where('email', 'minhtan090704@gmail.com')->first();
        if (!$user) {
            $this->command->error('Không tìm thấy minhtan090704@gmail.com. Vui lòng chạy MinhTanUserSeeder trước.');
            return;
        }

        $starter      = ArtistPackage::where('name', 'like', '%Khởi đầu%')->first();
        $standard     = ArtistPackage::where('name', 'like', '%Tiêu chuẩn%')->first();
        $professional = ArtistPackage::where('name', 'like', '%Chuyên nghiệp%')->first();

        if (!$starter || !$standard || !$professional) {
            $this->command->error('Chưa có gói nghệ sĩ. Vui lòng chạy ArtistPackageSeeder trước.');
            return;
        }

        // Xoá cũ
        $oldIds = ArtistRegistration::where('user_id', $user->id)
            ->whereNotIn('status', ['pending_payment', 'pending_review'])
            ->pluck('id');
        Payment::whereIn('payable_id', $oldIds)->where('payable_type', ArtistRegistration::class)->delete();
        ArtistRegistration::whereIn('id', $oldIds)->delete();

        $registrations = [
            // ── Lần 1: T12/2022 – bị từ chối ─────────────────────────────────
            [
                'submitted_at'     => Carbon::create(2022, 12, 5,  10, 0, 0),
                'reviewed_at'      => Carbon::create(2022, 12, 9,  14, 0, 0),
                'approved_at'      => null,
                'rejected_at'      => Carbon::create(2022, 12, 9,  14, 0, 0),
                'expires_at'       => null,
                'package'          => $starter,
                'status'           => ArtistRegistration::STATUS_REJECTED,
                'rejection_reason' => ArtistRegistration::REJECTION_REASON_PROFILE_INCOMPLETE,
                'stage_name'       => 'Minh Tân Official',
            ],
            // ── Lần 2: T03/2023 – Gói Khởi đầu, hiệu lực 1 năm ───────────────
            // Duyệt 10/03/2023 → hết hạn 10/03/2024
            [
                'submitted_at'     => Carbon::create(2023, 3, 8,   9,  0, 0),
                'reviewed_at'      => Carbon::create(2023, 3, 10,  11, 0, 0),
                'approved_at'      => Carbon::create(2023, 3, 10,  11, 0, 0),
                'rejected_at'      => null,
                'expires_at'       => Carbon::create(2024, 3, 10,  11, 0, 0), // đúng 365 ngày
                'package'          => $starter,
                'status'           => ArtistRegistration::STATUS_EXPIRED,
                'rejection_reason' => null,
                'stage_name'       => 'Minh Tân Official',
            ],
            // ── Lần 3: T03/2024 – Gói Tiêu chuẩn, hiệu lực 1 năm ────────────
            // Duyệt 12/03/2024 → hết hạn 12/03/2025
            [
                'submitted_at'     => Carbon::create(2024, 3, 11,  8,  30, 0),
                'reviewed_at'      => Carbon::create(2024, 3, 12,  10, 0, 0),
                'approved_at'      => Carbon::create(2024, 3, 12,  10, 0, 0),
                'rejected_at'      => null,
                'expires_at'       => Carbon::create(2025, 3, 12,  10, 0, 0), // đúng 365 ngày
                'package'          => $standard,
                'status'           => ArtistRegistration::STATUS_EXPIRED,
                'rejection_reason' => null,
                'stage_name'       => 'Minh Tân Official',
            ],
            // ── Lần 4: T03/2025 – Gói Chuyên nghiệp, hiệu lực 1 năm ──────────
            // Duyệt 10/03/2025 → hết hạn 10/03/2026 (< 16/04/2026 → expired)
            [
                'submitted_at'     => Carbon::create(2025, 3, 8,   9,  30, 0),
                'reviewed_at'      => Carbon::create(2025, 3, 10,  15, 0, 0),
                'approved_at'      => Carbon::create(2025, 3, 10,  15, 0, 0),
                'rejected_at'      => null,
                'expires_at'       => Carbon::create(2026, 3, 10,  15, 0, 0), // đúng 365 ngày, < hôm nay
                'package'          => $professional,
                'status'           => ArtistRegistration::STATUS_EXPIRED,
                'rejection_reason' => null,
                'stage_name'       => 'Minh Tân Official',
            ],
        ];

        foreach ($registrations as $data) {
            $reg = ArtistRegistration::create([
                'user_id'               => $user->id,
                'package_id'            => $data['package']->id,
                'submitted_stage_name'  => $data['stage_name'],
                'submitted_avt'         => $user->avatar,
                'submitted_cover_image' => null,
                'status'                => $data['status'],
                'rejection_reason'      => $data['rejection_reason'],
                'reviewed_at'           => $data['reviewed_at'],
                'approved_at'           => $data['approved_at'],
                'rejected_at'           => $data['rejected_at'],
                'expires_at'            => $data['expires_at'],
                'created_at'            => $data['submitted_at'],
                'updated_at'            => $data['reviewed_at'] ?? $data['submitted_at'],
            ]);

            $paymentStatus = ($data['status'] === ArtistRegistration::STATUS_REJECTED) ? 'failed' : 'paid';

            Payment::create([
                'user_id'                 => $user->id,
                'payable_type'            => ArtistRegistration::class,
                'payable_id'              => $reg->id,
                'provider'                => 'VNPAY',
                'method'                  => 'VNPAY',
                'amount'                  => $data['package']->price,
                'status'                  => $paymentStatus,
                'transaction_code'        => 'VNP_ART_' . strtoupper(Str::random(8)),
                'paid_at'                 => $paymentStatus === 'paid' ? $data['submitted_at'] : null,
                'provider_transaction_no' => 'MT_ART_' . strtoupper(Str::random(8)),
                'provider_pay_date'       => $data['submitted_at']->format('YmdHis'),
                'raw_response'            => ['seed' => true, 'note' => 'MinhTanArtistRegistrationSeeder'],
                'created_at'              => $data['submitted_at'],
                'updated_at'              => $data['submitted_at'],
            ]);

            $period = $data['expires_at']
                ? $data['approved_at']->format('d/m/Y') . ' → ' . $data['expires_at']->format('d/m/Y')
                    . ' (' . $data['package']->duration_days . ' ngày)'
                : 'Bị từ chối';

            $this->command->line("  ✓ [{$data['status']}] {$data['package']->name} | {$period}");
        }

        // Tất cả gói đều hết hạn trước hôm nay → gán role free
        if ($user->hasRole('artist')) {
            $user->removeRole('artist');
        }
        if (!$user->hasRole('premium') && !$user->hasRole('admin') && !$user->hasRole('free')) {
            $user->assignRole('free');
        }

        $this->command->info('✅ Seed hoàn tất — 4 lần đăng ký Nghệ sĩ (T12/2022 → T03/2026), tất cả đã hết hạn.');
    }
}
