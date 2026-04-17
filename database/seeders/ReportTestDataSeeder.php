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
use App\Models\ArtistProfile;
use App\Models\ArtistPackage;
use App\Models\ArtistRegistration;
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
                Vip::create(['id' => 'monthly', 'title' => 'Gói 1 Tháng', 'duration_days' => 30, 'price' => 49000, 'description' => 'Gói cơ bản', 'is_active' => true]);
                Vip::create(['id' => 'yearly', 'title' => 'Gói 1 Năm', 'duration_days' => 365, 'price' => 499000, 'description' => 'Siêu tiết kiệm', 'is_active' => true]);
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
                'avatar' => 'avt.jpg',
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

        // 4. Render Lượt đăng ký Nghệ sĩ & Doanh thu
        $this->command->info(">> Đang render lịch sử đăng ký Nghệ sĩ và Doanh thu...");
        $artistUsers = collect($newUserIds)->filter(fn (User $user) => $user->hasRole('artist'));
        $artistPkg = ArtistPackage::where('is_active', true)->first();
        
        if ($artistPkg) {
            foreach ($artistUsers as $au) {
                // Ngày đăng ký ngẫu nhiên trong quá khứ
                $regStart = Carbon::parse($au->created_at);
                if (rand(1, 100) <= 70) {
                    $regStart = Carbon::now()->subDays(rand(0, 120));
                }
                if ($regStart->isFuture()) $regStart = Carbon::now();

                $regEnd = (clone $regStart)->addDays($artistPkg->duration_days);
                
                // Tạo ArtistProfile cho user
                ArtistProfile::create([
                    'user_id' => $au->id,
                    'artist_package_id' => $artistPkg->id,
                    'stage_name' => $au->name,
                    'bio' => 'Hồ sơ nghệ sĩ được tạo tự động cho báo cáo.',
                    'avatar' => $au->avatar,
                    'status' => \App\Models\ArtistProfile::STATUS_ACTIVE,
                    'start_date' => $regStart,
                    'end_date' => $regEnd,
                ]);

                // Tạo ArtistRegistration
                $reg = ArtistRegistration::create([
                    'user_id' => $au->id,
                    'package_id' => $artistPkg->id,
                    'submitted_stage_name' => $au->name,
                    'status' => 'approved',
                    'reviewed_at' => $regStart,
                    'approved_at' => $regStart,
                    'expires_at' => $regEnd,
                    'created_at' => $regStart,
                    'updated_at' => $regStart
                ]);

                // Generate Payment VNPAY cho gói Nghệ sĩ
                Payment::create([
                    'user_id' => $au->id,
                    'payable_type' => ArtistRegistration::class,
                    'payable_id' => $reg->id,
                    'provider' => 'VNPAY',
                    'method' => 'VNPAY',
                    'amount' => $artistPkg->price,
                    'status' => 'paid',
                    'transaction_code' => 'ART_VNP_' . strtoupper(Str::random(10)),
                    'paid_at' => $regStart,
                    'provider_transaction_no' => 'ART_RPT_' . strtoupper(Str::random(8)),
                    'provider_pay_date' => $regStart->format('YmdHis'),
                    'raw_response' => [
                        'seed' => true,
                        'source' => 'ReportTestDataSeeder_Artist',
                    ],
                    'created_at' => $regStart,
                    'updated_at' => $regStart
                ]);
            }
        }

        // 5. Render lượt click banner trang chủ để tạo dữ liệu báo cáo
        $this->command->info(">> Đang tổng hợp views và clicks cho banner trang chủ...");
        if (Schema::hasTable('banners')) {
            DB::table('banners')->update(['clicks' => DB::raw('clicks + ' . rand(1000, 5000))]);
        }

        // 6. Render Lịch sử tìm kiếm (Search History) - Giải quyết vấn đề dữ liệu trống ở tab Nội dung
        $this->command->info(">> Đang render dữ liệu lịch sử tìm kiếm (Search History)...");
        $searchTerms = ['Sơn Tùng', 'MTP', 'Jack', 'Chill', 'Lo-fi', 'Pop', 'HH Beats', 'Poker Face', 'Bad Romance', 'Dark Horse', 'Despacito', 'Shape of you', 'Rain', 'Summer', 'Party'];
        $searchData = [];
        $users = User::where('deleted', false)->take(100)->get();
        
        if ($users->isNotEmpty()) {
            for ($i = 0; $i < 500; $i++) {
                $date = Carbon::now()->subDays(rand(0, 60));
                $searchData[] = [
                    'user_id' => $users->random()->id,
                    'query' => $searchTerms[array_rand($searchTerms)],
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            }
            DB::table('search_histories')->insert($searchData);
        }

        // 7. Render Lượt nghe bài hát (Cải tiến: Seed cho tất cả bài hát để dữ liệu đầy đặn)
        $this->command->info(">> Đang tổng hợp dữ liệu mật độ Lượt nghe hằng ngày cho TẤT CẢ bài hát...");
        $songs = Song::where('status', 'published')->get();
        if ($songs->count() > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;'); 
            DB::table('song_daily_stats')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $statsToInsert = [];
            foreach ($songs as $s) {
                // Đặt ngưỡng listens cao cho top songs để biểu đồ đẹp
                $isTop = rand(1, 100) > 80;
                $runningTotal = 0;

                for ($d = 60; $d >= 0; $d--) { // Tập trung 2 tháng gần nhất
                    $date = Carbon::today()->subDays($d);
                    $factor = (60 - $d) / 20; 
                    $weekendMult = ($date->isWeekend()) ? 1.8 : 1.0;
                    
                    $dailyPlay = rand(2, 20) + intval(rand(10, 50) * $factor * $weekendMult);
                    if ($isTop) {
                        $dailyPlay += rand(100, 300);
                    }
                    if ($d < 7) {
                        $dailyPlay += rand(50, 100); 
                    }

                    $runningTotal += $dailyPlay;

                    $statsToInsert[] = [
                        'song_id' => $s->id,
                        'stat_date' => $date->toDateString(),
                        'play_count' => $dailyPlay,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }
                $s->update(['listens' => $runningTotal]);
            }
            
            foreach (array_chunk($statsToInsert, 1000) as $chunk) {
                SongDailyStat::insert($chunk);
            }
        } else {
            $this->command->warn('Không tìm thấy bài hát published nào để seed lượt nghe.');
        }

        $this->command->info('==== VẼ ĐỒ THỊ VÀ KIẾN TẠO DỮ LIỆU THÀNH CÔNG ====');
    }
}
