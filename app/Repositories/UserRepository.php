<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\AccountHistory;
use App\Models\ArtistRegistration;
use App\Notifications\AccountUpdated;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    /**
     * Create a new user account.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'birthday' => $data['birthday'] ?? null,
                'gender' => $data['gender'] ?? null,
                'avatar' => $data['avatar'] ?? '/storage/avt.jpg',
                'status' => 'Đang hoạt động',
                'deleted' => false,
            ]);

            // Mặc định mọi user mới có role free.
            $user->syncRoles(['free']);

            // Create account history for registration
            $this->createHistory($user->id, $user->id, 'Đăng ký tài khoản mới', 'Đang hoạt động');

            return $user;
        });
    }

    /**
     * Find user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)
            ->where('deleted', false)
            ->first();
    }

    /**
     * Find user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return User::where('id', $id)
            ->where('deleted', false)
            ->first();
    }

    /**
     * Update user information.
     *
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    /**
     * Update user profile information and write account history.
     *
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function updateProfile(User $user, array $data): bool
    {
        return DB::transaction(function () use ($user, $data) {
            $emailChanged = array_key_exists('email', $data) && $data['email'] !== $user->email;

            $updated = $user->update($data);

            if ($updated) {
                $action = $emailChanged
                    ? 'Cập nhật hồ sơ và thay đổi email (chờ xác minh lại)'
                    : 'Cập nhật thông tin hồ sơ';

                $this->createHistory($user->id, $user->id, $action, $user->status);
            }

            return $updated;
        });
    }

    /**
     * Update artist profile information (nghệ danh, tiểu sử, ảnh bìa, mạng xã hội).
     *
     * @param User  $user
     * @param array $data  Validated data.
     * @return bool
     */
    public function updateArtistProfile(User $user, array $data): bool
    {
        return DB::transaction(function () use ($user, $data) {
            $updated = $user->update($data);

            if ($updated) {
                $this->createHistory(
                    $user->id,
                    $user->id,
                    'Cập nhật hồ sơ nghệ sĩ',
                    $user->status
                );
            }

            return $updated;
        });
    }

    /**
     * Update user password and write account history.
     * Uses direct DB write to guarantee the update is always persisted,
     * bypassing Eloquent dirty-checking which can silently skip updates.
     *
     * @param User $user
     * @param string $newPassword  Plain-text password; hashed here before write.
     * @return bool
     */
    public function updatePassword(User $user, string $newPassword): bool
    {
        return DB::transaction(function () use ($user, $newPassword) {
            // Direct DB write — avoids Eloquent isDirty() silent no-op
            $affected = DB::table('users')
                ->where('id', $user->id)
                ->update(['password' => Hash::make($newPassword)]);

            if ($affected > 0) {
                $user->refresh();
                $this->createHistory(
                    $user->id,
                    $user->id,
                    'Đổi mật khẩu tài khoản',
                    $user->status
                );
            }

            return $affected > 0;
        });
    }

    /**
     * Soft delete user.
     *
     * @param User $user
     * @param int $deletedBy
     * @return bool
     */
    public function softDelete(User $user, int $deletedBy): bool
    {
        return DB::transaction(function () use ($user, $deletedBy) {
            $result = $user->update(['deleted' => true, 'status' => 'Bị vô hiệu hóa']);
            
            if ($result) {
                $this->createHistory($user->id, $deletedBy, 'Vô hiệu hóa tài khoản', 'Bị vô hiệu hóa');
            }
            
            return $result;
        });
    }

    /**
     * Check if email exists.
     *
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = User::where('email', $email)->where('deleted', false);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if phone exists.
     *
     * @param string $phone
     * @param int|null $excludeId
     * @return bool
     */
    public function phoneExists(string $phone, ?int $excludeId = null): bool
    {
        $query = User::where('phone', $phone)->where('deleted', false);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Create account history record.
     *
     * @param int $userId
     * @param int $createdBy
     * @param string $action
     * @param string $status
     * @return AccountHistory
     */
    public function createHistory(int $userId, int $createdBy, string $action, string $status, ?string $lockReason = null): AccountHistory
    {
        return AccountHistory::create([
            'user_id'     => $userId,
            'created_by'  => $createdBy,
            'action'      => $action,
            'status'      => $status,
            'lock_reason' => $lockReason,
        ]);
    }

    /**
     * Get user's account history.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHistory(int $userId)
    {
        return AccountHistory::where('user_id', $userId)
            ->with('creator:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Verify user credentials.
     *
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public function verifyCredentials(string $email, string $password): ?User
    {
        $user = $this->findByEmail($email);

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }

        return null;
    }

    /**
     * Update last login.
     *
     * @param User $user
     * @return void
     */
    public function updateLastLogin(User $user): void
    {
        $this->createHistory(
            $user->id, 
            $user->id, 
            'Đăng nhập vào hệ thống', 
            $user->status
        );
    }

    // =========================================================================
    // Admin operations
    // =========================================================================

    /**
     * Get paginated users for admin list with optional filters.
     *
     * @param array $filters  ['search','role','status']
     * @param int   $perPage
     */
    public function getAdminUserList(array $filters = [], int $perPage = 20)
    {
        $query = User::query()->where('deleted', false);
        $today = now()->toDateString();

        if (! empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        // Single role filter takes priority over role_in
        if (! empty($filters['role'])) {
            if ($filters['role'] === 'premium') {
                $query->where(function ($q) use ($today) {
                    $q->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'premium'))
                      ->orWhereHas('subscriptions', function ($subQuery) use ($today) {
                          $subQuery->where('status', 'active')
                                   ->where('end_date', '>=', $today);
                      });
                });
            } else {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', $filters['role']));
            }
        } elseif (! empty($filters['role_in'])) {
            $roles = (array) $filters['role_in'];
            $hasPremium = in_array('premium', $roles, true);
            $nonPremiumRoles = array_values(array_filter($roles, fn ($role) => $role !== 'premium'));

            if (! $hasPremium) {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('slug', $roles));
            } else {
                $query->where(function ($q) use ($nonPremiumRoles, $today) {
                    if (! empty($nonPremiumRoles)) {
                        $q->whereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('slug', $nonPremiumRoles))
                            ->orWhere(function ($premiumQuery) use ($today) {
                            $premiumQuery->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'premium'))
                                ->orWhereHas('subscriptions', function ($subQuery) use ($today) {
                                    $subQuery->where('status', 'active')
                                        ->where('end_date', '>=', $today);
                                });
                        });

                        return;
                    }

                    $q->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'premium'))
                        ->orWhereHas('subscriptions', function ($subQuery) use ($today) {
                            $subQuery->where('status', 'active')
                                ->where('end_date', '>=', $today);
                        });
                });
            }
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }

    /**
     * Get paginated artist accounts with optional filters.
     */
    public function getAdminArtistList(array $filters = [], int $perPage = 20)
    {
        $query = User::query()
            ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'artist'))
            ->where('deleted', false);

        if (! empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if (isset($filters['verified']) && $filters['verified'] !== '') {
            if ($filters['verified'] === '1') {
                $query->whereNotNull('artist_verified_at');
            } else {
                $query->whereNull('artist_verified_at');
            }
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }

    /**
     * Toggle user account status between active and locked.
     * Cannot lock admin accounts.
     *
     * @param string|null $lockReason  Lý do khóa (chỉ dùng khi đang lock)
     */
    public function adminToggleStatus(User $user, int $adminId, ?string $lockReason = null): bool
    {
        return DB::transaction(function () use ($user, $adminId, $lockReason) {
            $isLocking = $user->status === 'Đang hoạt động';
            $newStatus = $isLocking ? 'Bị khóa' : 'Đang hoạt động';
            $action    = $isLocking ? 'Khóa tài khoản' : 'Mở khóa tài khoản';

            $updateData = ['status' => $newStatus];
            if ($isLocking) {
                $updateData['lock_reason'] = $lockReason;
            } else {
                $updateData['lock_reason'] = null; // xóa lý do khi mở khóa
            }

            $result = $user->update($updateData);

            if ($result) {
                $this->createHistory(
                    $user->id,
                    $adminId,
                    "[Admin] {$action}",
                    $newStatus,
                    $isLocking ? $lockReason : null   // lưu lý do khóa riêng cột
                );

                $event = $isLocking ? 'status_locked' : 'status_unlocked';
                $user->notify(new AccountUpdated($event, $isLocking ? $lockReason : null));
            }

            return $result;
        });
    }

    /**
     * Change a user's role.
     */
    public function adminChangeRole(User $user, string $newRole, int $adminId): bool
    {
        return DB::transaction(function () use ($user, $newRole, $adminId) {
            $oldRole = $user->role;
            $result  = true;

            // Hệ role mới: admin/free là vai trò độc quyền, artist/premium có thể cộng dồn.
            if ($newRole === 'admin') {
                $user->syncRoles(['admin']);
            } elseif ($newRole === 'free') {
                $user->syncRoles(['free']);
            } elseif (in_array($newRole, ['artist', 'premium'], true)) {
                if ($user->hasRole('admin')) {
                    // Admin không nằm trong luồng nâng cấp role user thường.
                    return false;
                }

                $roles = $user->getRoleNames();
                $roles = array_values(array_unique(array_filter(array_merge($roles, [$newRole]))));
                $roles = array_values(array_filter($roles, fn ($role) => in_array($role, ['artist', 'premium'], true)));

                if (empty($roles)) {
                    $roles = ['free'];
                }

                $user->syncRoles($roles);
            } else {
                return false;
            }

            $user->refresh();

            if ($result) {
                // Clear artist verification if demoted from artist
                if ($oldRole === 'artist' && ! $user->hasRole('artist')) {
                    $user->update(['artist_verified_at' => null]);
                }
                $this->createHistory(
                    $user->id, $adminId,
                    "[Admin] Đổi loại tài khoản: {$oldRole} → {$newRole}",
                    $user->status
                );
                $user->notify(new AccountUpdated('role_' . $newRole));
            }

            return $result;
        });
    }

    /**
     * Grant or revoke artist official verification (tick xanh).
     */
    public function adminToggleArtistVerified(User $user, int $adminId): bool
    {
        return DB::transaction(function () use ($user, $adminId) {
            $isVerified = $user->artist_verified_at !== null;
            $newValue   = $isVerified ? null : now();
            $action     = $isVerified ? 'Thu hồi xác minh nghệ sĩ' : 'Xác minh nghệ sĩ chính thức (tick xanh)';

            $result = $user->update(['artist_verified_at' => $newValue]);

            if ($result) {
                $this->createHistory($user->id, $adminId, "[Admin] {$action}", $user->status);
                $event = $isVerified ? 'artist_unverified' : 'artist_verified';
                $user->notify(new AccountUpdated($event));
            }

            return $result;
        });
    }

    /**
     * Thu hồi vĩnh viễn quyền Nghệ sĩ.
     * - Đặt artist_revoked_at = now()
     * - Hạ role về 'free' (không còn truy cập artist routes)
     * - Xóa tick xanh
     * - Gửi email + thông báo in-app cho user
     * - Songs/Albums KHÔNG bị xóa — giữ nguyên status hiện tại
     */
    public function adminRevokeArtist(User $user, int $adminId, string $reason): bool
    {
        return DB::transaction(function () use ($user, $adminId, $reason) {
            $result = $user->update([
                'artist_revoked_at'  => now(),
                'artist_verified_at' => null,
            ]);

            if ($result) {
                $user->removeRole('artist');
                if (! $user->hasRole('admin') && ! $user->hasRole('premium')) {
                    $user->assignRole('free');
                }

                $this->createHistory(
                    $user->id,
                    $adminId,
                    '[Admin] Thu hồi vĩnh viễn quyền Nghệ sĩ',
                    $user->status,
                    $reason
                );
                $user->notify(new AccountUpdated('artist_revoked', $reason));
            }

            return $result;
        });
    }

    /**
     * Admin soft-delete: mark user as deleted (cannot be undone via UI).
     */
    public function adminDelete(User $user, int $adminId): bool
    {
        return DB::transaction(function () use ($user, $adminId) {
            $result = $user->update([
                'deleted' => true,
                'status'  => 'Bị vô hiệu hóa',
            ]);

            if ($result) {
                $this->createHistory($user->id, $adminId, '[Admin] Xóa tài khoản', 'Bị vô hiệu hóa');
                $user->notify(new AccountUpdated('account_disabled'));
            }

            return $result;
        });
    }

    /**
     * Get aggregate stats for admin dashboard cards.
     */
    public function adminGetStats(): array
    {
        $base = User::where('deleted', false);
        $today = now()->toDateString();

        $premiumUsers = (clone $base)
            ->where(function ($q) use ($today) {
                $q->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'premium'))
                  ->orWhereHas('subscriptions', function ($subQuery) use ($today) {
                      $subQuery->where('status', 'active')
                               ->where('end_date', '>=', $today);
                  });
            })
            ->count();

        $freeUsers = (clone $base)
            ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'free'))
            ->whereDoesntHave('subscriptions', function ($subQuery) use ($today) {
                $subQuery->where('status', 'active')
                         ->where('end_date', '>=', $today);
            })
            ->count();

        return [
            'total'   => (clone $base)->count(),
            'free'    => $freeUsers,
            'premium' => $premiumUsers,
            'artist'  => (clone $base)->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'artist'))->count(),
            'locked'  => (clone $base)->where('status', 'Bị khóa')->count(),
            'new_month' => (clone $base)->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)->count(),
            'pending_unlock'  => AccountHistory::unlockRequests()->where('unlock_status', 'pending')->count(),
            'pending_artist'  => ArtistRegistration::where('status', 'pending_review')->count(),
        ];
    }

    /**
     * Admin creates a new user account.
     */
    public function adminCreateUser(array $data, int $adminId): User
    {
        return DB::transaction(function () use ($data, $adminId) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'phone'    => $data['phone'] ?? null,
                'birthday' => $data['birthday'] ?? null,
                'gender'   => $data['gender'] ?? null,
                'avatar'   => '/storage/avt.jpg',
                'status'   => 'Đang hoạt động',
                'deleted'  => false,
            ]);

            $seedRole = in_array(($data['role'] ?? 'free'), ['admin', 'free', 'premium', 'artist'], true)
                ? (string) $data['role']
                : 'free';

            if ($seedRole === 'admin') {
                $user->syncRoles(['admin']);
            } elseif ($seedRole === 'free') {
                $user->syncRoles(['free']);
            } else {
                $user->syncRoles([$seedRole]);
            }

            $this->createHistory($user->id, $adminId, '[Admin] Tạo tài khoản mới', 'Đang hoạt động');

            return $user;
        });
    }

    /**
     * Admin updates basic user info (name, email, phone, birthday, gender, role).
     */
    public function adminUpdateUser(User $user, array $data, int $adminId): bool
    {
        return DB::transaction(function () use ($user, $data, $adminId) {
            $result = $user->update($data);

            if ($result) {
                $this->createHistory($user->id, $adminId, '[Admin] Cập nhật thông tin tài khoản', $user->status);
            }

            return $result;
        });
    }

    /**
     * Admin force-resets a user's password.
     */
    public function adminResetPassword(User $user, string $newPassword, int $adminId): bool
    {
        return DB::transaction(function () use ($user, $newPassword, $adminId) {
            $affected = DB::table('users')
                ->where('id', $user->id)
                ->update(['password' => Hash::make($newPassword)]);

            if ($affected > 0) {
                $this->createHistory($user->id, $adminId, '[Admin] Đặt lại mật khẩu', $user->status);
            }

            return $affected > 0;
        });
    }
}
