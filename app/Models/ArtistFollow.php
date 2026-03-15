<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistFollow extends Model
{
    protected $fillable = [
        'user_id',
        'artist_id',
        'notify_in_app',
        'notify_email',
    ];

    protected $casts = [
        'notify_in_app' => 'boolean',
        'notify_email'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artist_id');
    }
}
