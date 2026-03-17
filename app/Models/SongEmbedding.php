<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongEmbedding extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'song_id',
        'embedding_type',
        'vector',
        'dimension',
        'model_version',
        'created_at',
    ];

    protected $casts = [
        'vector' => 'array',
        'dimension' => 'integer',
        'created_at' => 'datetime',
    ];

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class, 'song_id');
    }
}
