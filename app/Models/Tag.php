<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['type', 'slug', 'label'];

    // ─── Relations ─────────────────────────────────────────────────────────────

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'song_tags');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
