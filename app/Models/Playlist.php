<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Playlist extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'cover_image',
        'is_public',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function songs(): BelongsToMany
    {
        // Many-to-Many via playlist_song, with order mapping
        return $this->belongsToMany(Song::class, 'playlist_song')
                    ->withPivot(['id', 'sort_order'])
                    ->withTimestamps()
                    ->orderByPivot('sort_order', 'asc');
    }

    public function getCoverUrl(): string
    {
        if ($this->cover_image && Storage::disk('public')->exists($this->cover_image)) {
            return Storage::url($this->cover_image);
        }
        
        // Fallback: If playlist has no explicit custom cover, use first song cover
        $firstSong = $this->songs()->first();
        if ($firstSong) {
            return $firstSong->getCoverUrl();
        }

        return asset('images/disk.png');
    }
}
