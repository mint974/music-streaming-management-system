<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Genre extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'cover_image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    // ─── Boot ─────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate slug từ name khi tạo mới
        static::creating(function (Genre $genre) {
            if (empty($genre->slug)) {
                $genre->slug = static::uniqueSlug($genre->name);
            }
        });
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Tạo slug unique, tránh trùng với bản ghi khác (ngoại trừ bản thân khi update).
     */
    public static function uniqueSlug(string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;

        while (
            static::where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * Màu nền icon đảm bảo luôn có giá trị hợp lệ.
     */
    public function getColorAttribute(?string $value): string
    {
        return $value ?: '#6366f1';
    }

    /**
     * Icon FA với fallback.
     */
    public function getIconAttribute(?string $value): string
    {
        return $value ?: 'fa-solid fa-music';
    }

    /**
     * CSS cho ô icon (background rgba từ hex color).
     */
    public function iconBgStyle(): string
    {
        $hex = ltrim($this->color, '#');
        [$r, $g, $b] = sscanf($hex, '%02x%02x%02x') ?? [99, 102, 241];
        return "background:rgba({$r},{$g},{$b},.15);border:1px solid rgba({$r},{$g},{$b},.3)";
    }
}
