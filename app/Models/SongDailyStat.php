<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongDailyStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'song_id',
        'stat_date',
        'play_count',
    ];

    /**
     * The song this stat belongs to.
     */
    public function song()
    {
        return $this->belongsTo(Song::class, 'song_id');
    }
}
