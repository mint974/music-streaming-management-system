<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vip;
use App\Models\Subscription;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MinhTanSubscriptionSeeder extends Seeder
{
    /**
     * Mô phỏng mua gói VIP liên tục từ tháng 06/2025 đến tháng 03/2026 cho tài khoản dev.
     */
    public function run(): void
    {
        $user = User::where('email', 'minhtan090704@gmail.com')->first();

        if (!$user) {
            $this->command->error('Không tìm thấy tài khoản minhtan090704@gmail.com. Vui lòng chạy MinhTanUserSeeder trước.');
            return;
        }

        // Tìm gói 1 tháng để giả lập việc gia hạn hàng tháng (nếu chưa có thì tự tạo)
        $vip = Vip::where('duration_days', 30)->first();
        if (!$vip) {
            $vip = Vip::create([
                'id' => 'monthly',
                'title' => 'Gói 1 Tháng', 
                'duration_days' => 30, 
                'price' => 49000, 
                'description' => 'Gói gia hạn mô phỏng', 
                'is_active' => true
            ]);
        }

        // Bắt đầu từ 01/06/2025
        $startDate = Carbon::create(2025, 6, 1, 8, 30, 0); 
        
        // Reset dữ liệu cũ để tránh duplicate nếu chạy seeder nhiều lần
        $subscriptionIds = Subscription::where('user_id', $user->id)->pluck('id');
        Payment::whereIn('payable_id', $subscriptionIds)->where('payable_type', Subscription::class)->delete();
        Subscription::where('user_id', $user->id)->delete();

        // Từ 06/2025 đến hết 03/2026 là 10 lần mua liên tục
        for ($i = 0; $i < 10; $i++) {
            $subStart = $startDate->copy()->addDays($i * $vip->duration_days);
            $subEnd = $subStart->copy()->addDays($vip->duration_days);
            
            // Logic trạng thái chân thực: đến ngày hiện tại gói đã hết hạn hay vẫn còn
            $status = $subEnd->isPast() ? 'expired' : 'active';

            $sub = Subscription::create([
                'user_id' => $user->id,
                'vip_id' => $vip->id,
                'start_date' => $subStart->toDateString(),
                'end_date' => $subEnd->toDateString(),
                'status' => $status,
                'amount_paid' => $vip->price,
                // Time-travel history logging
                'created_at' => $subStart,
                'updated_at' => $subStart,
            ]);

            // Mỗi subscription phải có 1 payment tương ứng đã paid
            Payment::create([
                'user_id' => $user->id,
                'payable_type' => Subscription::class,
                'payable_id' => $sub->id,
                'provider' => 'VNPAY',
                'method' => 'VNPAY',
                'amount' => $vip->price,
                'status' => 'paid',
                'transaction_code' => 'VNP_' . strtoupper(Str::random(10)),
                'paid_at' => $subStart,
                'provider_transaction_no' => 'MT_SIMULATOR_' . strtoupper(Str::random(8)),
                'provider_pay_date' => $subStart->format('YmdHis'),
                'raw_response' => [
                    'seed' => true,
                    'note' => 'Giả lập đăng ký hàng tháng cho Minh Tân',
                ],
                'created_at' => $subStart,
                'updated_at' => $subStart,
            ]);
        }
        
        // Cập nhật lại Role cho chuẩn xác theo thời gian thực tế
        $hasActive = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($hasActive) {
            if (!$user->hasRole('premium')) {
                $user->assignRole('premium');
                if (!$user->hasRole('artist') && !$user->hasRole('admin')) {
                    $user->removeRole('free');
                }
            }
        } else {
            if ($user->hasRole('premium')) {
                $user->removeRole('premium');
            }
            if (!$user->hasRole('artist') && !$user->hasRole('admin') && !$user->hasRole('free')) {
                $user->assignRole('free');
            }
        }

        $this->command->info('Đã hoàn thiện giả lập hồ sơ VNPAY Premium cho minhtan090704@gmail.com (từ 06/2025 -> 03/2026).');
    }
}
