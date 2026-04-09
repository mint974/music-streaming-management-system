<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Song;
use App\Models\SongDailyStat;
use App\Models\ListeningHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ListeningStatController extends Controller
{
    /**
     * Ghi nhận 1 lượt nghe hợp lệ.
     */
    public function record(Request $request)
    {
        $validated = $request->validate([
            'song_id' => 'required|exists:songs,id',
            'played_percent' => 'required|numeric|min:0|max:100',
            'duration' => 'required|numeric|min:0'
        ]);

        // Kiểm tra cơ bản User Agent để loại bỏ bot
        $userAgent = strtolower($request->header('User-Agent'));
        if (preg_match('/bot|crawl|slurp|spider|mediapartners|curl|wget/i', $userAgent)) {
            return response()->json(['status' => 'ignored', 'message' => 'Lượt nghe bị bỏ qua (bot)']);
        }

        $songId = $validated['song_id'];
        $playedPercent = $validated['played_percent'];
        $duration = max(1, $validated['duration']);

        // Phải nghe >= 40%
        if ($playedPercent < 40) {
            return response()->json(['status' => 'ignored', 'message' => 'Chưa đạt 40% thời lượng nghe']);
        }

        // Chặn quá nhiều lượt từ một User/IP trong thời gian ngắn (< 50% thời lượng bài hát, tối đa 3 phút cho bài dài)
        $identifier = Auth::id() ?? $request->ip();
        $cacheKey = "record_listen_{$songId}_user_{$identifier}";
        $lastRecorded = Cache::get($cacheKey);

        // Khoảng thời gian cấm tính lại (50% thời lượng bài hoặc 1 phút tuỳ cái nào lớn hơn), max = 3 phút
        $lockSeconds = min(180, max(60, intval($duration * 0.5)));

        if ($lastRecorded && Carbon::parse($lastRecorded)->diffInSeconds(now()) < $lockSeconds) {
            return response()->json(['status' => 'blocked', 'message' => "Bị chặn do spam trong vòng {$lockSeconds}s (chưa đủ 50% thời lượng nghe)"]);
        }

        // Đánh dấu thời gian đã nghe
        Cache::put($cacheKey, now()->toDateTimeString(), now()->addDay());

        DB::transaction(function () use ($songId) {
            // Cập nhật lượt nghe theo ngày (nếu dòng chưa có -> tạo mới 0, sau đó +1)
            $stat = SongDailyStat::firstOrCreate(
                ['song_id' => $songId, 'stat_date' => now()->toDateString()],
                ['play_count' => 0]
            );
            $stat->increment('play_count');

            // Cập nhật tổng số lượt nghe vào bảng songs chính
            Song::withoutTimestamps(function () use ($songId) {
                Song::where('id', $songId)->increment('listens');
            });

            // Ghi log chi tiết lịch sử cá nhân (chỉ dành cho user đã đăng nhập)
            if (Auth::check()) {
                ListeningHistory::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'song_id' => $songId,
                    ],
                    [
                        'source' => 'stream',
                        'listened_at' => now(),
                    ]
                );
            }
        });

        return response()->json(['status' => 'success', 'message' => 'Đã ghi nhận 1 lượt nghe hợp lệ.']);
    }
}
