<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Vip;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\SongDailyStat;
use App\Models\Song;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ReportTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('==== KHỞI TẠO DỮ LIỆU ĐỒ QUY MÔ LỚN CHO BÁO CÁO THỐNG KÊ ====');

        // 1. Tạo gói VIP nếu hệ thống rỗng
        $vips = tap(Vip::all(), function ($collection) {
            if ($collection->isEmpty()) {
                Vip::create(['title' => 'Gói 1 Tháng', 'duration_days' => 30, 'price' => 49000, 'description' => 'Gói cơ bản', 'is_active' => true]);
                Vip::create(['title' => 'Gói 1 Năm', 'duration_days' => 365, 'price' => 499000, 'description' => 'Siêu tiết kiệm', 'is_active' => true]);
            }
        });
        $vips = Vip::all();

        // 2. Tạo 400 người dùng rải rác trong 1 năm với độ tuổi và vai trò khác nhau
        $totalUsersToCreate = 400;
        $this->command->info(">> Đang render {$totalUsersToCreate} users (kèm role và độ tuổi) rải rác trong 1 năm qua...");
        
        $newUserIds = [];
        $pwd = Hash::make('123456');

        // Tránh lỗi duplicate nếu chạy seeder nhiều lần (Xoá fake users cũ đi dựa trên prefix email)
        DB::table('users')->where('email', 'like', 'report_user_%@test.com')->delete();

        for ($i = 0; $i < $totalUsersToCreate; $i++) {
            // Phân bổ thời gian: 60% đăng ký trong 3 tháng gần nhất, 40% cũ hơn => Tạo được Trend tăng trưởng đẹp
            if (rand(1, 100) > 40) {
                $createdAt = Carbon::now()->subDays(rand(0, 90))->startOfDay();
            } else {
                $createdAt = Carbon::now()->subDays(rand(91, 365))->startOfDay();
            }

            // Phân bổ Role: 65% free, 25% premium, 10% artist
            $roleRand = rand(1, 100);
            if ($roleRand <= 65) {
                $role = 'free';
            } elseif ($roleRand <= 90) {
                $role = 'premium';
            } else {
                $role = 'artist';
            }

            // Phân bổ độ tuổi
            $ageGroupRand = rand(1, 100);
            if ($ageGroupRand <= 15) { // Dưới 18
                $birthday = Carbon::now()->subYears(rand(12, 17));
            } elseif ($ageGroupRand <= 55) { // 18-24 (chiếm đa số theo trend nhạc)
                $birthday = Carbon::now()->subYears(rand(18, 24));
            } elseif ($ageGroupRand <= 85) { // 25-34
                $birthday = Carbon::now()->subYears(rand(25, 34));
            } else { // Trên 35
                $birthday = Carbon::now()->subYears(rand(35, 50));
            }

            $user = User::create([
                'name' => 'Report User ' . Str::random(4),
                'email' => "report_user_{$i}_" . uniqid() . "@test.com",
                'password' => $pwd,
                'status' => 'Đang hoạt động',
                'birthday' => $birthday->toDateString(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);

            $user->syncRoles([$role]);
            $newUserIds[] = $user;
        }

        // 3. Render Lượt đăng ký Premium & Doanh thu thanh toán
        $this->command->info(">> Đang render lịch sử mua gói V.I.P và Doanh thu tháng/năm...");
        $premiumUsers = collect($newUserIds)->filter(fn (User $user) => $user->hasRole('premium'));
        foreach ($premiumUsers as $pu) {
            $pkg = $vips->random();
            
            // Ngày mua có khả năng nằm ở 3 tháng gần đây để biểu đồ Doanh thu nảy số mạnh ở tháng này
            $subStart = Carbon::parse($pu->created_at);
            if (rand(1, 100) <= 70) {
                $subStart = Carbon::now()->subDays(rand(0, 90));
            }
            // Đảm bảo startDate không thể vượt quá hiện tại
            if ($subStart->isFuture()) {
                $subStart = Carbon::now();
            }
            $subEnd = (clone $subStart)->addDays($pkg->duration_days);
            $status = $subEnd->isFuture() ? 'active' : 'expired';
            
            // Create Subscription
            $sub = Subscription::create([
                'user_id' => $pu->id,
                'vip_id' => $pkg->id,
                'start_date' => $subStart->toDateString(),
                'end_date' => $subEnd->toDateString(),
                'status' => $status,
                'amount_paid' => $pkg->price,
                'created_at' => $subStart,
                'updated_at' => $subStart
            ]);

            // Generate Payment VNPAY
            Payment::create([
                'user_id' => $pu->id,
                'payable_type' => \App\Models\Subscription::class,
                'payable_id' => $sub->id,
                'provider' => 'VNPAY',
                'method' => 'VNPAY',
                'amount' => $pkg->price,
                'status' => 'paid',
                'transaction_code' => 'VNP_' . strtoupper(Str::random(10)),
                'paid_at' => $subStart,
                'provider_transaction_no' => 'RPT_' . strtoupper(Str::random(8)),
                'provider_pay_date' => $subStart->format('YmdHis'),
                'raw_response' => [
                    'seed' => true,
                    'source' => 'ReportTestDataSeeder',
                ],
                'created_at' => $subStart,
                'updated_at' => $subStart
            ]);
        }

        // 4. Render Lượt clicks banner để giả lập doanh thu Quảng cáo
        $this->command->info(">> Đang tổng hợp views và clicks cho Banner Advertising...");
        if (Schema::hasTable('banners')) {
            DB::table('banners')->update(['clicks' => DB::raw('clicks + ' . rand(1000, 5000))]);
        }

        // 5. Render Lượt nghe bài hát 365 ngày qua
        $this->command->info(">> Đang tổng hợp dữ liệu mật độ Lượt nghe hằng ngày (song_daily_stats)...");
        $songs = Song::where('status', 'published')->take(10)->get();
        if ($songs->count() > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Ngăn lỗi foreign keys nếu có ràng buộc xóa
            DB::table('song_daily_stats')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $statsToInsert = [];
            foreach ($songs as $s) {
                $baseListens = $s->listens ?? 0;
                // Mỗi bài hát tạo một bản ghi daily count suốt 365 ngày
                for ($d = 365; $d >= 0; $d--) {
                    $date = Carbon::today()->subDays($d);
                    
                    // Logic trend: Bài mới càng ngày càng hot.
                    // Hệ số tăng thêm nếu ngày đó là cuối tuần
                    $factor = (365 - $d) / 100; // Càng gần hôm nay factor càng cao (0 lên 3.65)
                    $weekendMult = ($date->isWeekend()) ? 1.5 : 1.0;
                    
                    // Giả lập từ 5 - 150 lượt nghe mỗi ngày một bài x hệ số x cuối tuần
                    $dailyPlay = rand(5, 50) + intval(rand(10, 30) * $factor * $weekendMult);
                    if ($d < 30) {
                        $dailyPlay += rand(50, 150); // Trending 1 tháng trở lại
                    }

                    $baseListens += $dailyPlay;

                    $statsToInsert[] = [
                        'song_id' => $s->id,
                        'stat_date' => $date->toDateString(),
                        'play_count' => $dailyPlay,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }
                // Đồng bộ tổng số lượt nghe mới cho bài hát
                $s->update(['listens' => $baseListens]);
            }
            
            // Bulk insert chunk
            foreach (array_chunk($statsToInsert, 1000) as $chunk) {
                SongDailyStat::insert($chunk);
            }
        } else {
            $this->command->warn('Không tìm thấy bài hát published nào để seed lượt nghe.');
        }

        $this->command->info('==== VẼ ĐỒ THỊ VÀ KIẾN TẠO DỮ LIỆU THÀNH CÔNG ====');
    }
}
