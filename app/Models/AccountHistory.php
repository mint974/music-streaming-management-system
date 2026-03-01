<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'action',
        'lock_reason',
        'status',
        'user_id',
        'created_by',
        // Unlock request fields
        'content',
        'unlock_status',
        'admin_note',
        'handled_by',
        'handled_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'handled_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeHistories($query)
    {
        return $query->where('type', 'history');
    }

    public function scopeUnlockRequests($query)
    {
        return $query->where('type', 'unlock_request');
    }

    public function scopePending($query)
    {
        return $query->where('unlock_status', 'pending');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isUnlockRequest(): bool { return $this->type === 'unlock_request'; }
    public function isPending(): bool       { return $this->unlock_status === 'pending';  }
    public function isApproved(): bool      { return $this->unlock_status === 'approved'; }
    public function isRejected(): bool      { return $this->unlock_status === 'rejected'; }
}
