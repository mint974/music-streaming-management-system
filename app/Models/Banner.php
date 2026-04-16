<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    protected $fillable = [
        'created_by',
        'title',
        'image_path',
        'target_url',
        'status',
        'start_time',
        'end_time',
        'order_index',
        'clicks',
    ];

    protected $casts = [
        'created_by'  => 'integer',
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image_path ? asset($this->image_path) : asset('images/disk.png');
    }
}
