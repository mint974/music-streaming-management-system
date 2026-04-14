<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistFollow extends Model
{
    protected $fillable = [
        'user_id',
        'followed_artist_profile_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function followedArtistProfile(): BelongsTo
    {
        return $this->belongsTo(ArtistProfile::class, 'followed_artist_profile_id');
    }
}
