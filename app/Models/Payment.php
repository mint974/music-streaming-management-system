<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'payable_type',
        'payable_id',
        'provider',
        'method',
        'amount',
        'status',
        'transaction_code',
        'provider_transaction_no',
        'provider_pay_date',
        'paid_at',
        'raw_response',
        'refund_amount',
        'refunded_at',
    ];

    protected $casts = [
        'paid_at'       => 'datetime',
        'refunded_at'   => 'datetime',
        'amount'        => 'integer',
        'refund_amount' => 'integer',
        'raw_response'  => 'array',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mỗi payment thuộc 1 subscription.
     */
    public function getSubscriptionAttribute(): ?Subscription
    {
        return $this->payable instanceof Subscription ? $this->payable : null;
    }

    /**
     * Mỗi payment co the thuoc 1 don dang ky nghe si.
     */
    public function getArtistRegistrationAttribute(): ?ArtistRegistration
    {
        return $this->payable instanceof ArtistRegistration ? $this->payable : null;
    }

    public function getDateAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->paid_at;
    }

    public function setDateAttribute($value): void
    {
        $this->attributes['paid_at'] = $value;
    }

    public function getVnpTransactionNoAttribute(): ?string
    {
        return $this->provider_transaction_no;
    }

    public function getVnpPayDateAttribute(): ?string
    {
        return $this->provider_pay_date;
    }

    /**
     * Mỗi payment co the trace ve user tao giao dich.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
        return $this->refund_amount !== null
            && $this->refund_amount > 0
            && $this->refunded_at !== null;
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
