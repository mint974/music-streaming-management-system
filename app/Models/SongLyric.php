<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongLyric extends Model
{
    use HasFactory;

    protected $fillable = [
        'song_id',
        'name',
        'language_code',
        'source',
        'is_default',
        'is_visible',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_visible' => 'boolean',
    ];

    public function song()
    {
        return $this->belongsTo(Song::class);
    }

    public function lines()
    {
        return $this->hasMany(SongLyricLine::class)->orderBy('line_order');
    }

    public function getTypeAttribute(): string
    {
        if ($this->relationLoaded('lines')) {
            $hasTiming = $this->lines->contains(static fn (SongLyricLine $line) => $line->start_time_ms !== null);
            return $hasTiming ? 'synced' : 'plain';
        }

        $hasTiming = $this->lines()->whereNotNull('start_time_ms')->exists();
        return $hasTiming ? 'synced' : 'plain';
    }

    public function getRawTextAttribute(): string
    {
        $lines = $this->relationLoaded('lines')
            ? $this->lines
            : $this->lines()->get(['content']);

        return $lines
            ->pluck('content')
            ->map(static fn ($text) => trim((string) $text))
            ->implode("\n");
    }

    public function getStatusAttribute(): string
    {
        return 'verified';
    }

    public function getIsVerifiedAttribute(): bool
    {
        return true;
    }
}
