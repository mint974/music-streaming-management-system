<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'vip_id',
        'start_date',
        'end_date',
        'status',
        'amount_paid',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'amount_paid' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    /**
     * Mỗi lượt đăng ký thuộc 1 tài khoản người dùng.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mỗi lượt đăng ký thuộc 1 gói VIP.
     */
    public function vip(): BelongsTo
    {
        return $this->belongsTo(Vip::class, 'vip_id');
    }

    /**
     * Mỗi subscription có 1 payment (được tạo khi thanh toán qua VNPAY).
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'subscription_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Số ngày còn lại (0 nếu đã hết hạn / không active).
     */
    public function daysRemaining(): int
    {
        if (! $this->isActive()) {
            return 0;
        }

        $days = now()->startOfDay()->diffInDays($this->end_date, false);
        return max(0, (int) $days);
    }

    /**
     * Nhãn trạng thái hiển thị tiếng Việt.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Chờ thanh toán',
            'active'    => 'Đang hiệu lực',
            'expired'   => 'Đã hết hạn',
            'cancelled' => 'Đã hủy',
            default     => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending'   => 'warning',
            'active'    => 'success',
            'expired'   => 'secondary',
            'cancelled' => 'danger',
            default     => 'secondary',
        };
    }
}
