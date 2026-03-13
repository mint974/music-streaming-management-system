<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Song extends Model
{
    protected $fillable = [
        'user_id',
        'genre_id',
        'album_id',
        'title',
        'author',
        'duration',
        'file_path',
        'file_mime',
        'file_size',
        'cover_image',
        'lyrics',
        'lyrics_type',
        'released_date',
        'publish_at',
        'is_vip',
        'status',
        'listens',
        'deleted',
    ];

    protected $casts = [
        'released_date' => 'date',
        'publish_at'    => 'datetime',
        'is_vip'        => 'boolean',
        'deleted'       => 'boolean',
        'listens'       => 'integer',
        'duration'      => 'integer',
        'file_size'     => 'integer',
    ];

    // ─── Available tags ────────────────────────────────────────────────────────

    public static array $MOOD_TAGS = [
        'vui-ve'    => 'Vui vẻ',
        'buon'      => 'Buồn',
        'lang-man'  => 'Lãng mạn',
        'energetic' => 'Energetic',
        'thu-gian'  => 'Thư giãn',
        'hao-hung'  => 'Hào hùng',
        'tuc-gian'  => 'Tức giận',
        'tam-trang' => 'Tâm trạng',
    ];

    public static array $ACTIVITY_TAGS = [
        'tap-gym'    => 'Tập gym',
        'chay-bo'    => 'Chạy bộ',
        'lam-viec'   => 'Làm việc',
        'lai-xe'     => 'Lái xe',
        'yoga'       => 'Yoga',
        'ngu'        => 'Ngủ',
        'hoc-tap'    => 'Học tập',
        'tiec-tung'  => 'Tiệc tùng',
    ];

    public static array $TOPIC_TAGS = [
        'tinh-yeu'  => 'Tình yêu',
        'chia-tay'  => 'Chia tay',
        'gia-dinh'  => 'Gia đình',
        'que-huong' => 'Quê hương',
        'cuoc-song' => 'Cuộc sống',
        'tuoi-tre'  => 'Tuổi trẻ',
        'buon-vui'  => 'Buồn vui',
        'ky-uc'     => 'Ký ức',
    ];

    // ─── Relations ─────────────────────────────────────────────────────────────

    public function artist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'song_tags');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted', false);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('deleted', false);
    }

    public function scopeForArtist(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)->where('deleted', false);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function getCoverUrl(): string
    {
        if ($this->cover_image && Storage::disk('public')->exists($this->cover_image)) {
            return Storage::url($this->cover_image);
        }

        return asset('images/default-song.png');
    }

    public function getAudioUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function durationFormatted(): string
    {
        $minutes = intdiv($this->duration, 60);
        $seconds = $this->duration % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'published' => 'Đã xuất bản',
            'pending'   => 'Chờ duyệt',
            'scheduled' => 'Hẹn giờ xuất bản',
            'hidden'    => 'Đã ẩn',
            'draft'     => 'Bản nháp',
            default     => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'pending'   => 'warning',
            'scheduled' => 'info',
            'hidden'    => 'dark',
            'draft'     => 'secondary',
            default     => 'secondary',
        };
    }

    public function getMoodTags(): array
    {
        return $this->tags->where('type', 'mood')->pluck('slug')->toArray();
    }

    public function getActivityTags(): array
    {
        return $this->tags->where('type', 'activity')->pluck('slug')->toArray();
    }

    public function getTopicTags(): array
    {
        return $this->tags->where('type', 'topic')->pluck('slug')->toArray();
    }

    public function fileSizeFormatted(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        return round($bytes / 1024, 1) . ' KB';
    }
}
