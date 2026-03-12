<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArtistPackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'is_active',
    ];

    protected $casts = [
        'price'         => 'integer',
        'duration_days' => 'integer',
        'is_active'     => 'boolean',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Relations ────────────────────────────────────────────────────────────
    public function features(): HasMany
    {
        return $this->hasMany(ArtistPackageFeature::class, 'package_id')
                    ->orderBy('sort_order');
    }
    public function registrations(): HasMany
    {
        return $this->hasMany(ArtistRegistration::class, 'package_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function formattedPrice(): string
    {
        return number_format($this->price) . ' ₫';
    }
}
