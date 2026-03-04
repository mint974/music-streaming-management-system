<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ArtistRegistration;
use App\Models\Subscription;
use App\Models\AccountHistory;
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
        'artist_verified_at',
        // Artist profile
        'artist_name',
        'bio',
        'cover_image',
        'social_links',
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
        'social_links'        => 'array',
    ];

    /**
     * Kiểm tra user có yêu cầu mở khóa đang chờ xử lý không.
     */
    public function hasPendingUnlockRequest(): bool
    {
        return AccountHistory::where('user_id', $this->id)
            ->where('type', 'unlock_request')
            ->where('unlock_status', 'pending')
            ->exists();
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
     * Lấy tên hiển thị của nghệ sĩ (nghệ danh ưu tiên hơn tên thật).
     */
    public function getDisplayArtistName(): string
    {
        return $this->artist_name ?: $this->name;
    }

    /**
     * Lấy URL ảnh đại diện, fallback về SVG avatar.
     */
    public function getAvatarUrl(): string
    {
        if ($this->avatar && $this->avatar !== '/storage/avt.jpg') {
            return asset($this->avatar);
        }
        $initial  = strtoupper(substr($this->name, 0, 1));
        $encoded  = rawurlencode($initial);
        return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Ccircle cx='80' cy='80' r='80' fill='%23a855f7'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='56' fill='%23ffffff' font-weight='bold'%3E{$encoded}%3C/text%3E%3C/svg%3E";
    }

    /**
     * Lấy danh sách mạng xã hội đã điền, bỏ qua giá trị rỗng.
     */
    public function getSocialLinksFiltered(): array
    {
        $links = $this->social_links ?? [];
        return array_filter($links, fn ($v) => !empty(trim((string) $v)));
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
     * Also returns true if user has an active subscription even if role wasn't synced.
     * Auto-syncs role to 'premium' if a live subscription is found with role=free.
     */
    public function isPremium(): bool
    {
        if ($this->role === 'premium') return true;
        // Fallback: active subscription exists (guards against role/subscription sync issues)
        if (!in_array($this->role, ['admin', 'artist']) && $this->activeSubscription() !== null) {
            // Auto-fix the inconsistency silently (DB + in-memory)
            $this->role = 'premium';
            $this->saveQuietly();
            return true;
        }
        return false;
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

    // ─── Artist registration relations ──────────────────────────────────────────

    /**
     * Các đơn đăng ký trở thành nghệ sĩ.
     */
    public function artistRegistrations(): HasMany
    {
        return $this->hasMany(ArtistRegistration::class, 'user_id')->latest();
    }

    /**
     * Kiểm tra user có đơn đăng ký nghệ sĩ đang chờ xử lý không.
     */
    public function hasPendingArtistRegistration(): bool
    {
        return $this->artistRegistrations()
            ->whereIn('status', ['pending_payment', 'pending_review'])
            ->exists();
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
