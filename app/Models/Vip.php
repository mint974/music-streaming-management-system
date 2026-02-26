<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vip extends Model
{
    // String primary key (not auto-incrementing)
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'description',
        'duration_days',
        'price',
        'is_active',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'price'         => 'integer',
        'is_active'     => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    /**
     * Một gói VIP có nhiều lượt đăng ký.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'vip_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Số lượng đăng ký đang hoạt động của gói này.
     */
    public function activeSubscribersCount(): int
    {
        return $this->subscriptions()->where('status', 'active')->count();
    }

    /**
     * Tổng doanh thu từ gói này (VNĐ).
     */
    public function totalRevenue(): int
    {
        return (int) $this->subscriptions()
            ->whereIn('status', ['active', 'expired'])
            ->sum('amount_paid');
    }

    /**
     * Nhãn tên hiển thị với giá.
     */
    public function labelWithPrice(): string
    {
        return "{$this->title} — " . number_format($this->price) . ' ₫';
    }

    /**
     * Scope: chỉ lấy gói đang kích hoạt.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
