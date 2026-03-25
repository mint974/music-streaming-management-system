<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongLyricLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'song_lyric_id',
        'line_order',
        'start_time_ms',
        'end_time_ms',
        'content',
    ];

    public function lyric()
    {
        return $this->belongsTo(SongLyric::class, 'song_lyric_id');
    }
}
