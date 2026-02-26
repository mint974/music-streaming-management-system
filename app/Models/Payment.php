<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'subscription_id',
        'method',
        'status',
        'transaction_code',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    /**
     * Mỗi payment thuộc 1 subscription.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Nhãn trạng thái tiếng Việt + màu badge Bootstrap.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Đang chờ',
            'paid'    => 'Đã thanh toán',
            'failed'  => 'Thất bại',
            default   => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'paid'    => 'success',
            'failed'  => 'danger',
            default   => 'secondary',
        };
    }
}
