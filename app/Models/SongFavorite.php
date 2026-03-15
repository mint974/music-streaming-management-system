<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongFavorite extends Model
{
    protected $fillable = [
        'user_id',
        'song_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class, 'song_id');
    }
}
