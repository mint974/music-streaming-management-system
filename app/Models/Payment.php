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
        'vnp_transaction_no',
        'vnp_pay_date',
        'date',
        'refund_amount',
        'refunded_at',
    ];

    protected $casts = [
        'date'          => 'datetime',
        'refunded_at'   => 'datetime',
        'refund_amount' => 'integer',
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

    public function isRefunded(): bool
    {
        return $this->refund_amount !== null && $this->refund_amount > 0;
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
