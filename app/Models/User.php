<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Subscription;
use App\Models\UnlockRequest;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'avatar',
        'email',
        'birthday',
        'gender',
        'password',
        'phone',
        'role',
        'status',
        'lock_reason',
        'deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'   => 'datetime',
        'artist_verified_at'  => 'datetime',
        'birthday'            => 'date',
        'deleted'             => 'boolean',
        'password'            => 'hashed',
    ];

    /**
     * Yêu cầu mở khóa tài khoản do user gửi.
     */
    public function unlockRequests(): HasMany
    {
        return $this->hasMany(UnlockRequest::class, 'user_id')->latest();
    }

    /**
     * Kiểm tra user có yêu cầu mở khóa đang chờ xử lý không.
     */
    public function hasPendingUnlockRequest(): bool
    {
        return $this->unlockRequests()->where('status', 'pending')->exists();
    }

    /**
     * Get the account histories for the user.
     */
    public function accountHistories(): HasMany
    {
        return $this->hasMany(AccountHistory::class, 'user_id');
    }

    /**
     * Get the histories created by this user.
     */
    public function createdHistories(): HasMany
    {
        return $this->hasMany(AccountHistory::class, 'created_by');
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'Đang hoạt động' && !$this->deleted;
    }

    /**
     * Check if user is locked.
     */
    public function isLocked(): bool
    {
        return $this->status === 'Bị khóa';
    }

    /**
     * Check if artist has official verification (tick xanh).
     */
    public function isArtistVerified(): bool
    {
        return $this->role === 'artist' && $this->artist_verified_at !== null;
    }

    /**
     * Check if user is admin.
     * Role: admin — Full system management.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is an artist (Nghệ sĩ).
     * Role: artist — Upload songs, manage albums, view own play stats.
     */
    public function isArtist(): bool
    {
        return $this->role === 'artist';
    }

    /** @deprecated Use isArtist() instead. Kept for backward compatibility. */
    public function isSinger(): bool
    {
        return $this->isArtist();
    }

    /**
     * Check if user is a free listener (Thính giả miễn phí).
     * Role: free — Listen with ads, limited features.
     */
    public function isFree(): bool
    {
        return $this->role === 'free';
    }

    /**
     * Check if user is a premium listener (Thính giả Premium).
     * Role: premium — No ads, offline download, high quality.
     */
    public function isPremium(): bool
    {
        return $this->role === 'premium';
    }

    /**
     * Check if user can access premium content (premium or admin).
     */
    public function canAccessPremium(): bool
    {
        return in_array($this->role, ['premium', 'admin']);
    }

    /**
     * Check if user can upload/manage music (artist or admin).
     */
    public function canManageMusic(): bool
    {
        return in_array($this->role, ['artist', 'admin']);
    }

    // ─── Subscription relations ───────────────────────────────────────────────

    /**
     * Một tài khoản có nhiều lượt đăng ký gói VIP.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'user_id')->latest();
    }

    /**
     * Lượt đăng ký đang hiệu lực hiện tại (null nếu không có).
     * Chỉ trả về subscription status=active VÀ end_date >= hôm nay.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->first();
    }
}
