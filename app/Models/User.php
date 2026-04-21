<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\ArtistRegistration;
use App\Models\Subscription;
use App\Models\AccountHistory;
use App\Models\Song;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

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
        'status',
        'lock_reason',
        'deleted',
        'is_onboarded',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'   => 'datetime',
        'birthday'            => 'date',
        'deleted'             => 'boolean',
        'password'            => 'hashed',
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
     * Get the social links for this user (replaces JSON social_links column).
     */
    public function socialLinks(): HasManyThrough
    {
        return $this->hasManyThrough(
            UserSocialLink::class,
            ArtistProfile::class,
            'user_id',
            'artist_profile_id',
            'id',
            'id'
        );
    }

    public function artistProfile(): HasOne
    {
        return $this->hasOne(ArtistProfile::class, 'user_id');
    }

    public function getArtistNameAttribute($value): ?string
    {
        return $this->artistProfile?->stage_name ?? $value;
    }

    public function getBioAttribute($value): ?string
    {
        return $this->artistProfile?->bio ?? $value;
    }

    public function getCoverImageAttribute($value): ?string
    {
        return $this->artistProfile?->cover_image ?? $value;
    }

    public function getArtistVerifiedAtAttribute($value)
    {
        return $this->artistProfile?->verified_at ?? $value;
    }

    public function getArtistRevokedAtAttribute($value)
    {
        return $this->artistProfile?->revoked_at ?? $value;
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
        return $this->isArtist() && $this->artist_verified_at !== null;
    }

    /**
     * Quan hệ nhiều-nhiều: user có nhiều role.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
            ->withPivot('granted_at');
    }

    /**
     * Danh sách slug role của user.
     * Có fallback cho cột users.role để tương thích dữ liệu cũ.
     */
    public function getRoleNames(): array
    {
        if ($this->relationLoaded('roles')) {
            $slugs = $this->roles->pluck('slug')->filter()->values()->all();
            if (! empty($slugs)) {
                return $slugs;
            }
        }

        $slugs = $this->roles()->pluck('slug')->filter()->values()->all();
        if (! empty($slugs)) {
            return $slugs;
        }

        return [];
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoleNames(), true);
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole((string) $role)) {
                return true;
            }
        }

        return false;
    }

    public function assignRole(string $role): void
    {
        $roleId = Role::query()->where('slug', $role)->value('id');
        if (! $roleId) {
            return;
        }

        $this->roles()->syncWithoutDetaching([
            $roleId => ['granted_at' => now()],
        ]);
        $this->unsetRelation('roles');
    }

    public function removeRole(string $role): void
    {
        $roleId = Role::query()->where('slug', $role)->value('id');
        if (! $roleId) {
            return;
        }

        $this->roles()->detach($roleId);
        $this->unsetRelation('roles');
    }

    public function syncRoles(array $roles): void
    {
        $roleIds = Role::query()
            ->whereIn('slug', $roles)
            ->pluck('id')
            ->all();

        $syncPayload = [];
        foreach ($roleIds as $roleId) {
            $syncPayload[$roleId] = ['granted_at' => now()];
        }

        $this->roles()->sync($syncPayload);
        $this->unsetRelation('roles');
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
        $effectiveAvatar = $this->artistProfile?->avatar ?: $this->avatar;

        if ($effectiveAvatar && $effectiveAvatar !== '/storage/avt.jpg') {
            return asset($effectiveAvatar);
        }
        $nameForInitial = $this->name ?: ($this->artist_name ?: 'U');
        $initial  = mb_strtoupper(mb_substr($nameForInitial, 0, 1, 'UTF-8'), 'UTF-8');
        $encoded = strtr($initial, [
            '%' => '%25',
            '#' => '%23',
            '<' => '%3C',
            '>' => '%3E',
            ' ' => '%20',
        ]);
        return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Ccircle cx='80' cy='80' r='80' fill='%23a855f7'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='56' fill='%23ffffff' font-weight='bold'%3E{$encoded}%3C/text%3E%3C/svg%3E";
    }

    /**
     * Lấy danh sách mạng xã hội đã điền, bỏ qua giá trị rỗng.
     * Returns ['facebook' => 'https://...', 'instagram' => 'https://...']
     */
    public function getSocialLinksFiltered(): array
    {
        return $this->socialLinks
            ->pluck('url', 'platform')
            ->filter(fn ($v) => !empty(trim((string) $v)))
            ->toArray();
    }

    /**
     * Danh sách trường hồ sơ nghệ sĩ còn thiếu trước khi admin xét duyệt.
     *
     * @return array<int, string>
     */
    public function missingArtistProfileFieldsForRegistration(): array
    {
        $this->loadMissing('socialLinks');

        $missing = [];

        if (trim((string) $this->artist_name) === '') {
            $missing[] = 'Tên nghệ danh';
        }

        if (trim((string) $this->bio) === '') {
            $missing[] = 'Tiểu sử nghệ sĩ';
        }

        $avatar = trim((string) ($this->artistProfile?->avatar ?: $this->avatar));
        if ($avatar === '' || $avatar === '/storage/avt.jpg') {
            $missing[] = 'Ảnh đại diện';
        }

        if (trim((string) $this->cover_image) === '') {
            $missing[] = 'Ảnh bìa kênh';
        }

        $requiredPlatforms = ['facebook', 'instagram', 'youtube', 'tiktok'];
        $social = $this->socialLinks->pluck('url', 'platform');

        foreach ($requiredPlatforms as $platform) {
            $url = trim((string) ($social[$platform] ?? ''));
            if ($url === '') {
                $missing[] = 'Liên kết ' . ucfirst($platform);
            }
        }

        return $missing;
    }

    /**
     * Kiểm tra hồ sơ nghệ sĩ đã đủ thông tin để admin xét duyệt hay chưa.
     */
    public function isArtistProfileCompleteForRegistration(): bool
    {
        return count($this->missingArtistProfileFieldsForRegistration()) === 0;
    }

    /**
     * Check if user is admin.
     * Role: admin — Full system management.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is an artist (Nghệ sĩ).
     * Role: artist — Upload songs, manage albums, view own play stats.
     */
    public function isArtist(): bool
    {
        return $this->hasRole('artist');
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
        return ! $this->isAdmin() && ! $this->isArtist() && ! $this->isPremium();
    }

    /**
     * Check if user is a premium listener (Thính giả Premium).
     * Role: premium — No ads, offline download, high quality.
     * Also returns true if user has an active subscription even if role wasn't synced.
     * Auto-syncs role to 'premium' if a live subscription is found with role=free.
     */
    public function isPremium(): bool
    {
        return $this->hasRole('premium') || $this->activeSubscription() !== null;
    }

    /**
     * Check if user can access premium content (premium or admin).
     */
    public function canAccessPremium(): bool
    {
        return $this->isAdmin() || $this->isPremium();
    }

    /**
     * Check if user can upload/manage music (artist or admin).
     * Returns false when the artist package has expired or quyền bị thu hồi.
     */
    public function canManageMusic(): bool
    {
        if ($this->isAdmin()) return true;
        return $this->isArtist() && !$this->isArtistPackageExpired();
    }

    /**
     * Kiểm tra quyền nghệ sĩ đã bị thu hồi vĩnh viễn bởi admin.
     */
    public function isArtistRevoked(): bool
    {
        return $this->artist_revoked_at !== null;
    }

    /**
     * Lấy đăng ký nghệ sĩ đang còn hiệu lực (chưa hết hạn).
     */
    public function activeArtistRegistration(): ?ArtistRegistration
    {
        return $this->artistRegistrations()
            ->where('status', 'approved')
            ->where('expires_at', '>=', now())
            ->first();
    }

    /**
     * Kiểm tra gói nghệ sĩ đã hết hạn.
     * True khi role=artist nhưng không có đăng ký approved nào còn hiệu lực.
     */
    public function isArtistPackageExpired(): bool
    {
        if (!$this->isArtist()) return false;
        return $this->activeArtistRegistration() === null;
    }

    // ─── Artist restrictions ───────────────────────────────────────────────

    /**
     * Get active package restrictions if explicitly parsed from descriptions.
     */
    public function canCreateMoreSongs(): array
    {
        $reg = $this->activeArtistRegistration();
        if (!$reg || !$reg->package) return ['ok' => false, 'message' => 'Bạn chưa có gói nghệ sĩ nào.'];

        $max = $reg->package->max_songs;
        if ($max === null) return ['ok' => true]; // Unlimited

        $count = $this->songs()
                      ->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year)
                      ->count();

        if ($count >= $max) {
            return ['ok' => false, 'message' => "Bạn đã đạt giới hạn tải lên {$max} bài hát trong tháng này theo quyền lợi gói."];
        }
        return ['ok' => true];
    }

    public function canCreateMoreAlbums(): array
    {
        $reg = $this->activeArtistRegistration();
        if (!$reg || !$reg->package) return ['ok' => false, 'message' => 'Bạn chưa có gói nghệ sĩ nào.'];

        $max = $reg->package->max_albums;
        if ($max === null) return ['ok' => true]; // Unlimited

        // Tổng số album Artist đã tạo (ko tính xóa)
        $count = $this->albums()->where('deleted', false)->count();

        if ($count >= $max) {
            return ['ok' => false, 'message' => "Bạn đã đạt giới hạn tạo tối đa {$max} album."];
        }
        return ['ok' => true];
    }

    /**
     * Quan hệ Model user -> album
     */
    public function albums(): HasManyThrough
    {
        return $this->hasManyThrough(
            Album::class,
            ArtistProfile::class,
            'user_id',
            'artist_profile_id',
            'id',
            'id'
        );
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

    /**
     * Trả về thời điểm user có thể đăng ký nghệ sĩ lại sau khi bị từ chối.
     * Cooldown: 3 ngày kể từ reviewed_at của đơn bị từ chối gần nhất.
     * Trả về null nếu không trong thời gian chờ.
     */
    public function artistReapplyCooldownEnds(): ?\Carbon\Carbon
    {
        $lastRejected = $this->artistRegistrations()
            ->where('status', 'rejected')
            ->whereNotNull('reviewed_at')
            ->latest('reviewed_at')
            ->first();

        if (!$lastRejected) {
            return null;
        }

        $canReapplyAt = $lastRejected->reviewed_at->addDays(3);

        return $canReapplyAt->isFuture() ? $canReapplyAt : null;
    }

    /**
     * Kiểm tra user có đang trong thời gian chờ đăng ký lại nghệ sĩ không.
     */
    public function isArtistReapplyCooldown(): bool
    {
        return $this->artistReapplyCooldownEnds() !== null;
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
            ->with('vip', 'payment')
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->first();
    }

    // ─── Listener data relations ────────────────────────────────────────────

    public function artistFollows(): HasMany
    {
        return $this->hasMany(ArtistFollow::class, 'user_id');
    }

    public function followers(): HasManyThrough
    {
        return $this->hasManyThrough(
            ArtistFollow::class,
            ArtistProfile::class,
            'user_id',
            'followed_artist_profile_id',
            'id',
            'id'
        );
    }

    public function savedAlbums(): HasMany
    {
        return $this->hasMany(SavedAlbum::class, 'user_id');
    }

    public function listeningHistories(): HasMany
    {
        return $this->hasMany(ListeningHistory::class, 'user_id');
    }

    public function songFavorites(): HasMany
    {
        return $this->hasMany(SongFavorite::class, 'user_id');
    }

    public function songs(): HasManyThrough
    {
        return $this->hasManyThrough(
            Song::class,
            ArtistProfile::class,
            'user_id',
            'artist_profile_id',
            'id',
            'id'
        );
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(DatabaseNotification::class, 'user_id')->latest();
    }

    public function readNotifications(): HasMany
    {
        return $this->notifications()->read();
    }

    public function unreadNotifications(): HasMany
    {
        return $this->notifications()->unread();
    }

    public function notificationSetting(): HasOne
    {
        return $this->hasOne(NotificationSetting::class, 'user_id');
    }

    public function playlists(): HasMany
    {
        return $this->hasMany(Playlist::class, 'user_id');
    }

    public function followedArtists()
    {
        return User::query()
            ->join('artist_profiles', 'artist_profiles.user_id', '=', 'users.id')
            ->join('artist_follows', 'artist_follows.followed_artist_profile_id', '=', 'artist_profiles.id')
            ->where('artist_follows.user_id', $this->id)
            ->select('users.*')
            ->distinct();
    }
}
