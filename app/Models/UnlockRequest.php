<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnlockRequest extends Model
{
    protected $fillable = [
        'user_id',
        'content',
        'status',
        'admin_note',
        'handled_by',
        'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    /** Người dùng gửi yêu cầu. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Scope: chưa được xử lý. */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool  { return $this->status === 'pending';  }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
}
