<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    protected $fillable = ['user_id', 'query'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lưu query vào lịch sử của user (tránh trùng lặp, giữ tối đa 20 mục).
     */
    public static function record(int $userId, string $query): void
    {
        $query = trim($query);
        if (empty($query)) return;

        // Xóa bản ghi cũ giống query này (nếu có) để tránh trùng
        static::where('user_id', $userId)
            ->whereRaw('LOWER(query) = ?', [mb_strtolower($query)])
            ->delete();

        // Tạo bản ghi mới (mới nhất lên đầu)
        static::create(['user_id' => $userId, 'query' => $query]);

        // Giữ tối đa 20 mục gần nhất
        $ids = static::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->skip(20)
            ->take(PHP_INT_MAX)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            static::whereIn('id', $ids)->delete();
        }
    }

    /**
     * Lấy lịch sử tìm kiếm gần đây của user.
     */
    public static function recent(int $userId, int $limit = 8): array
    {
        return static::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->pluck('query')
            ->toArray();
    }
}
