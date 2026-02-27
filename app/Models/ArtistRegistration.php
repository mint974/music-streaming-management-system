<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistRegistration extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'artist_name',
        'bio',
        'status',
        'amount_paid',
        'transaction_code',
        'paid_at',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'amount_paid' => 'integer',
        'paid_at'     => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ArtistPackage::class, 'package_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ─── Status helpers ────────────────────────────────────────────────────────

    public function isPendingPayment(): bool
    {
        return $this->status === 'pending_payment';
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'pending_review';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending_payment' => 'Chờ thanh toán',
            'pending_review'  => 'Chờ xét duyệt',
            'approved'        => 'Đã phê duyệt',
            'rejected'        => 'Bị từ chối',
            default           => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending_payment' => 'warning',
            'pending_review'  => 'info',
            'approved'        => 'success',
            'rejected'        => 'danger',
            default           => 'secondary',
        };
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
