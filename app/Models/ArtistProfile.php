<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArtistProfile extends Model
{
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'user_id',
        'artist_package_id',
        'stage_name',
        'bio',
        'avatar',
        'cover_image',
        'verified_at',
        'status',
        'revoked_at',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'revoked_at' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function artistPackage(): BelongsTo
    {
        return $this->belongsTo(ArtistPackage::class, 'artist_package_id');
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(UserSocialLink::class, 'artist_profile_id');
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class, 'artist_profile_id');
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class, 'artist_profile_id');
    }
}