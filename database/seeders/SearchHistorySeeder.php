<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Song;
use App\Models\ArtistProfile;
use Carbon\Carbon;

class SearchHistorySeeder extends Seeder
{
    /**
     * Seed lịch sử tìm kiếm của người dùng dựa theo tên bài hát
     * và nghệ sĩ thực tế trong hệ thống.
     * 
     * Logic:
     * - 60% query bắt nguồn từ tên bài hát (đúng / viết tắt / nửa tên)
     * - 25% query bắt nguồn từ tên nghệ sĩ (stage_name)
     * - 15% query mà hệ thống KHÔNG CÓ kết quả (để thống kê nhu cầu thị trường)
     */
    public function run(): void
    {
        $this->command->info('==== SEED LỊCH SỬ TÌM KIẾM ====');

        // Xóa dữ liệu cũ của seeder (giữ lại search history thực tế của user thật)
        DB::table('search_histories')
            ->where('query', 'like', '%')
            ->whereIn('user_id', function ($q) {
                $q->select('id')->from('users')->where('email', 'like', 'report_user_%@test.com');
            })
            ->delete();

        // Lấy tất cả users hợp lệ (bao gồm report_user và users thực)
        $userIds = DB::table('users')
            ->where('deleted', false)
            ->pluck('id')
            ->toArray();

        if (empty($userIds)) {
            $this->command->warn('Không có user nào trong hệ thống. Vui lòng chạy UserSeeder trước.');
            return;
        }

        // Lấy bài hát published
        $songs = DB::table('songs')
            ->where('status', 'published')
            ->where('deleted', false)
            ->select('id', 'title')
            ->get();

        // Lấy danh sách nghệ sĩ active
        $artists = DB::table('artist_profiles')
            ->whereIn('status', ['active', 'inactive'])
            ->select('id', 'stage_name')
            ->get();

        if ($songs->isEmpty()) {
            $this->command->warn('Không có bài hát published nào. Vui lòng chạy CustomSongsSeeder trước.');
            return;
        }

        $userCount = count($userIds);
        $this->command->info(">> Tìm thấy {$songs->count()} bài hát, {$artists->count()} nghệ sĩ, {$userCount} users.");

        // =====================================================================
        // Danh sách query "không có kết quả" - từ khóa thị trường chưa được đáp ứng
        // =====================================================================
        $noResultQueries = [
            // Nghệ sĩ quốc tế phổ biến
            'BTS', 'BLACKPINK', 'Taylor Swift', 'Ed Sheeran', 'Billie Eilish',
            'The Weeknd', 'Dua Lipa', 'Justin Bieber', 'Ariana Grande', 'Post Malone',
            // Nghệ sĩ Việt chưa có trên hệ thống
            'Sơn Tùng MTP', 'Hoà Minzy', 'Bích Phương', 'Đen Vâu', 'Tlinh',
            'Mỹ Tâm', 'Hà Anh Tuấn', 'Đức Phúc', 'Dương Hoàng Yến', 'Vũ Cát Tường',
            // Bài hát nổi tiếng chưa có bản quyền
            'Chúng ta của hiện tại', 'Muộn rồi mà sao còn', 'Nơi này có anh',
            'Bật tình yêu lên', 'Hãy trao cho anh', 'Có chắc yêu là đây',
            'Waiting for You', 'Shape of You', 'Blinding Lights',
            // Thể loại/mood chưa có
            'nhạc thiền định', 'nhạc tập gym', 'nhạc lo-fi học bài', 'nhạc jazz café',
            'nhạc buồn tâm trạng', 'nhạc chill đêm khuya', 'nhạc underground Việt',
            'rap Việt underground', 'indie acoustic guitar', 'nhạc phim Hàn',
            // Podcast (hệ thống chưa có)
            'podcast kinh doanh', 'podcast tâm lý', 'podcast tiếng Anh',
            'podcast thể thao', 'podcast sức khoẻ',
        ];

        // =====================================================================
        // Sinh query từ tên bài hát (đúng hoặc biến thể tiếng Việt)
        // =====================================================================
        $songQueries = [];
        foreach ($songs as $song) {
            $title = $song->title;
            $words = explode(' ', $title);
            $wordCount = count($words);

            // Query đầy đủ
            $songQueries[] = $title;
            // Query viết thường
            $songQueries[] = mb_strtolower($title, 'UTF-8');

            // Nửa tên đầu nếu có nhiều hơn 1 từ
            if ($wordCount >= 2) {
                $halfLen = intdiv($wordCount, 2);
                $songQueries[] = implode(' ', array_slice($words, 0, max(1, $halfLen)));
            }

            // Chỉ 1-2 từ đầu (kiểu gõ vội)
            if ($wordCount >= 3) {
                $songQueries[] = implode(' ', array_slice($words, 0, 2));
            }

            // Thêm "nghe" hoặc "bài" prefix kiểu người Việt hay gõ
            $songQueries[] = 'nghe ' . mb_strtolower($title, 'UTF-8');
            $songQueries[] = 'bài ' . $words[0];
        }

        // =====================================================================
        // Sinh query từ tên nghệ sĩ
        // =====================================================================
        $artistQueries = [];
        foreach ($artists as $artist) {
            $name = $artist->stage_name;
            $artistQueries[] = $name;
            $artistQueries[] = mb_strtolower($name, 'UTF-8');
            $artistQueries[] = 'nhạc của ' . $name;
            $artistQueries[] = $name . ' mới nhất';
            $artistQueries[] = 'ca sĩ ' . $name;
        }

        // =====================================================================
        // Insert dữ liệu với phân phối thực tế
        // =====================================================================
        $toInsert = [];
        $totalInserted = 0;

        // Số lượng records theo nhóm
        $totalRecords = 1500;
        $songQueryCount   = (int) ($totalRecords * 0.60); // 60% tìm bài hát
        $artistQueryCount = (int) ($totalRecords * 0.25); // 25% tìm nghệ sĩ
        $noResultCount    = $totalRecords - $songQueryCount - $artistQueryCount; // 15% không có kết quả

        // Hàm sinh timestamp ngẫu nhiên trong khoảng
        $randomTime = function (int $dayFrom, int $dayTo): Carbon {
            return Carbon::now()->subDays(rand($dayFrom, $dayTo))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
        };

        // --- Nhóm 1: Từ khóa bài hát ---
        for ($i = 0; $i < $songQueryCount; $i++) {
            $query  = $songQueries[array_rand($songQueries)];
            $userId = $userIds[array_rand($userIds)];
            $at     = $randomTime(0, 90);

            $toInsert[] = [
                'user_id'    => $userId,
                'query'      => $query,
                'created_at' => $at,
                'updated_at' => $at,
            ];
        }

        // --- Nhóm 2: Từ khóa nghệ sĩ ---
        for ($i = 0; $i < $artistQueryCount; $i++) {
            $query  = !empty($artistQueries) ? $artistQueries[array_rand($artistQueries)] : 'nghệ sĩ';
            $userId = $userIds[array_rand($userIds)];
            $at     = $randomTime(0, 90);

            $toInsert[] = [
                'user_id'    => $userId,
                'query'      => $query,
                'created_at' => $at,
                'updated_at' => $at,
            ];
        }

        // --- Nhóm 3: Từ khóa không có kết quả ---
        for ($i = 0; $i < $noResultCount; $i++) {
            $query  = $noResultQueries[array_rand($noResultQueries)];
            $userId = $userIds[array_rand($userIds)];
            // Phân phối rải 90 ngày, hot hơn ở 30 ngày gần đây
            $at = rand(1, 100) <= 60 ? $randomTime(0, 30) : $randomTime(31, 90);

            $toInsert[] = [
                'user_id'    => $userId,
                'query'      => $query,
                'created_at' => $at,
                'updated_at' => $at,
            ];
        }

        // Bulk insert theo chunk
        foreach (array_chunk($toInsert, 500) as $chunk) {
            DB::table('search_histories')->insert($chunk);
            $totalInserted += count($chunk);
        }

        $this->command->info(">> Đã seed {$totalInserted} lịch sử tìm kiếm thành công.");
        $this->command->info("   - Từ khoá bài hát: {$songQueryCount}");
        $this->command->info("   - Từ khoá nghệ sĩ: {$artistQueryCount}");
        $this->command->info("   - Từ khoá không có kết quả: {$noResultCount}");
        $this->command->info('==== SEED LỊCH SỬ TÌM KIẾM HOÀN TẤT ====');
    }
}
