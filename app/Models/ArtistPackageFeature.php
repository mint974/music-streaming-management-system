<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistPackageFeature extends Model
{
    public $timestamps = false;

    protected $fillable = ['package_id', 'feature', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    // ─── Relations ─────────────────────────────────────────────────────────────

    public function package(): BelongsTo
    {
        return $this->belongsTo(ArtistPackage::class, 'package_id');
    }
}
