<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArtistPackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'features',
        'price',
        'is_active',
    ];

    protected $casts = [
        'features'  => 'array',
        'price'     => 'integer',
        'is_active' => 'boolean',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Relations ────────────────────────────────────────────────────────────

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
