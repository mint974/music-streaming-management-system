<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'notify_new_song',
        'notify_new_album',
        'notify_in_app',
        'notify_email',
    ];

    protected $casts = [
        'notify_new_song'  => 'boolean',
        'notify_new_album' => 'boolean',
        'notify_in_app'    => 'boolean',
        'notify_email'     => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
