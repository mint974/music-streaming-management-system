<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSocialLink extends Model
{
    public $timestamps = false;

    protected $fillable = ['artist_profile_id', 'platform', 'url'];

    // ─── Relations ─────────────────────────────────────────────────────────────

    public function artistProfile(): BelongsTo
    {
        return $this->belongsTo(ArtistProfile::class, 'artist_profile_id');
    }
}
