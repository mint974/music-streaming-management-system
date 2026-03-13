<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Album extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'cover_image',
        'released_date',
        'status',
        'deleted',
    ];

    protected $casts = [
        'released_date' => 'date',
        'deleted'       => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function artist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class)->where('deleted', false);
    }

    public function allSongs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

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

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getCoverUrl(): string
    {
        if ($this->cover_image && Storage::disk('public')->exists($this->cover_image)) {
            return Storage::url($this->cover_image);
        }

        return asset('images/default-album.png');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'published' => 'Đã xuất bản',
            'draft'     => 'Bản nháp',
            default     => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'draft'     => 'secondary',
            default     => 'secondary',
        };
    }

    public function getSongCount(): int
    {
        return $this->songs()->count();
    }
}
