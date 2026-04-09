<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongLyric extends Model
{
    use HasFactory;

    protected $fillable = [
        'song_id',
        'name',
        'language_code',
        'type',
        'source',
        'status',
        'raw_text',
        'is_default',
        'is_visible',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_visible' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function song()
    {
        return $this->belongsTo(Song::class);
    }

    public function lines()
    {
        return $this->hasMany(SongLyricLine::class)->orderBy('line_order');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
