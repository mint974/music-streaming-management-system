<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'created_by',
        'title',
        'image_path',
        'audio_path',
        'target_url',
        'type',
        'status',
        'start_time',
        'end_time',
        'order_index',
        'clicks',
    ];

    protected $casts = [
        'created_by'  => 'integer',
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image_path ? asset($this->image_path) : asset('images/disk.png');
    }

    public function getAudioUrlAttribute(): ?string
    {
        return $this->audio_path ? asset($this->audio_path) : null;
    }

    public function hasAudioFile(): bool
    {
        if (empty($this->audio_path)) {
            return false;
        }

        $path = str_replace('/storage/', '', $this->audio_path);

        return Storage::disk('public')->exists($path);
    }

    public function isHero(): bool
    {
        return $this->type === 'hero';
    }

    public function isAd(): bool
    {
        return $this->type === 'ad';
    }
}
