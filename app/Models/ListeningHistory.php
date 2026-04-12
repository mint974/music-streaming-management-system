<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListeningHistory extends Model
{
    protected $fillable = [
        'user_id',
        'song_id',
        'played_seconds',
        'played_percent',
        'is_completed',
        'listened_at',
    ];

    protected $casts = [
        'played_seconds' => 'integer',
        'played_percent' => 'float',
        'is_completed' => 'boolean',
        'listened_at' => 'datetime',
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
