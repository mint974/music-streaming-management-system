<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongFeature extends Model
{
    protected $fillable = [
        'song_id',
        'danceability',
        'energy',
        'valence',
        'acousticness',
        'instrumentalness',
        'speechiness',
        'liveness',
        'tempo',
        'loudness',
        'feature_source',
    ];

    protected $casts = [
        'danceability' => 'float',
        'energy' => 'float',
        'valence' => 'float',
        'acousticness' => 'float',
        'instrumentalness' => 'float',
        'speechiness' => 'float',
        'liveness' => 'float',
        'tempo' => 'float',
        'loudness' => 'float',
    ];

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class, 'song_id');
    }
}
