<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ArtistRegistration extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    public const REJECTION_REASON_PROFILE_INCOMPLETE = 'profile_incomplete';
    public const REJECTION_REASON_IDENTITY_UNVERIFIED = 'identity_unverified';
    public const REJECTION_REASON_POLICY_VIOLATION = 'policy_violation';
    public const REJECTION_REASON_COPYRIGHT_RISK = 'copyright_risk';
    public const REJECTION_REASON_SPAM_RISK = 'spam_risk';
    public const REJECTION_REASON_OTHER = 'other';

    protected $fillable = [
        'user_id',
        'package_id',
        'submitted_stage_name',
        'submitted_avt',
        'submitted_cover_image',
        'status',
        'admin_note',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'expires_at',
    ];

    protected $casts = [
        'reviewed_at'        => 'datetime',
        'approved_at'        => 'datetime',
        'rejected_at'        => 'datetime',
        'expires_at'         => 'datetime',
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

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ─── Status helpers ────────────────────────────────────────────────────────

    /**
     * Backward-compat: map old attribute artist_name to submitted_stage_name.
     */
    public function getArtistNameAttribute(): ?string
    {
        return $this->submitted_stage_name;
    }

    /**
     * Backward-compat: map old attribute bio from current profile data.
     */
    public function getBioAttribute(): ?string
    {
        return $this->user?->bio;
    }

    /**
     * Backward-compat payment amount from package.
     */
    public function getAmountPaidAttribute(): int
    {
        return (int) ($this->package?->price ?? 0);
    }

    public function getTransactionCodeAttribute(): ?string
    {
        return $this->payment?->transaction_code;
    }

    public function getVnpTransactionNoAttribute(): ?string
    {
        return $this->payment?->provider_transaction_no ?? $this->payment?->vnp_transaction_no;
    }

    public function getVnpPayDateAttribute(): ?string
    {
        return $this->payment?->provider_pay_date ?? $this->payment?->vnp_pay_date;
    }

    public function getPaidAtAttribute()
    {
        return $this->payment?->date;
    }

    public function getRefundAmountAttribute(): ?int
    {
        return $this->payment?->refund_amount;
    }

    public function getRefundedAtAttribute()
    {
        return $this->payment?->refunded_at;
    }

    public function getRefundStatusAttribute(): ?string
    {
        $payment = $this->payment;

        if (! $payment || ! $payment->isPaid() || (int) ($payment->refund_amount ?? 0) <= 0) {
            return null;
        }

        return $payment->refunded_at ? 'completed' : 'pending';
    }

    public function isPendingPayment(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function isPendingReview(): bool
    {
        return $this->status === self::STATUS_PENDING_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isRefunded(): bool
    {
        return $this->isRefundCompleted();
    }

    public function isRefundPending(): bool
    {
        return $this->refund_status === 'pending';
    }

    public function isRefundCompleted(): bool
    {
        return $this->refund_status === 'completed';
    }

    public function refundStatusLabel(): string
    {
        return match ($this->refund_status) {
            'pending'   => 'Chờ hoàn tiền',
            'completed' => 'Đã hoàn tiền',
            default     => '—',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_PAYMENT => 'Chờ thanh toán',
            self::STATUS_PENDING_REVIEW  => 'Chờ xét duyệt',
            self::STATUS_APPROVED        => 'Đã phê duyệt',
            self::STATUS_REJECTED        => 'Bị từ chối',
            self::STATUS_EXPIRED         => 'Đã hết hạn',
            default           => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_PAYMENT => 'warning',
            self::STATUS_PENDING_REVIEW  => 'info',
            self::STATUS_APPROVED        => 'success',
            self::STATUS_REJECTED        => 'danger',
            self::STATUS_EXPIRED         => 'secondary',
            default           => 'secondary',
        };
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public static function rejectionReasonOptions(): array
    {
        return [
            self::REJECTION_REASON_PROFILE_INCOMPLETE => 'Hồ sơ nghệ sĩ chưa đầy đủ',
            self::REJECTION_REASON_IDENTITY_UNVERIFIED => 'Chưa xác minh đủ thông tin danh tính',
            self::REJECTION_REASON_POLICY_VIOLATION => 'Nội dung hồ sơ chưa phù hợp chính sách',
            self::REJECTION_REASON_COPYRIGHT_RISK => 'Rủi ro bản quyền hoặc quyền sở hữu nội dung',
            self::REJECTION_REASON_SPAM_RISK => 'Hành vi bất thường hoặc nghi ngờ spam',
            self::REJECTION_REASON_OTHER => 'Lý do khác',
        ];
    }

    public function rejectionReasonLabel(): string
    {
        if (! $this->rejection_reason) {
            return 'Chưa phân loại';
        }

        return self::rejectionReasonOptions()[$this->rejection_reason] ?? 'Chưa phân loại';
    }

    public function rejectionNextStepGuidance(): string
    {
        return match ($this->rejection_reason) {
            self::REJECTION_REASON_PROFILE_INCOMPLETE => 'Bổ sung nghệ danh, phần giới thiệu và thông tin hồ sơ chi tiết hơn trước khi gửi lại.',
            self::REJECTION_REASON_IDENTITY_UNVERIFIED => 'Chuẩn bị thông tin xác minh danh tính rõ ràng và nhất quán với hồ sơ tài khoản.',
            self::REJECTION_REASON_POLICY_VIOLATION => 'Điều chỉnh hồ sơ để phù hợp Điều khoản sử dụng và chính sách cộng đồng của nền tảng.',
            self::REJECTION_REASON_COPYRIGHT_RISK => 'Rà soát quyền sử dụng nội dung, chỉ gửi lại khi đảm bảo quyền sở hữu/hợp pháp.',
            self::REJECTION_REASON_SPAM_RISK => 'Giảm thao tác gửi lặp, cập nhật hồ sơ minh bạch và gửi lại một lần đầy đủ.',
            default => 'Bạn có thể cập nhật hồ sơ và gửi lại sau thời gian chờ theo hướng dẫn trên hệ thống.',
        };
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        $allowedTransitions = [
            self::STATUS_PENDING_PAYMENT => [self::STATUS_PENDING_REVIEW],
            self::STATUS_PENDING_REVIEW  => [self::STATUS_APPROVED, self::STATUS_REJECTED],
            self::STATUS_APPROVED        => [self::STATUS_EXPIRED],
            self::STATUS_REJECTED        => [],
            self::STATUS_EXPIRED         => [],
        ];

        return in_array($targetStatus, $allowedTransitions[$this->status] ?? [], true);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePendingReview($query)
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
