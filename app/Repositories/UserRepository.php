<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\AccountHistory;
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
                'role' => $data['role'] ?? 'user',
                'status' => 'Đang hoạt động',
                'deleted' => false,
            ]);

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
    public function createHistory(int $userId, int $createdBy, string $action, string $status): AccountHistory
    {
        return AccountHistory::create([
            'user_id' => $userId,
            'created_by' => $createdBy,
            'action' => $action,
            'status' => $status,
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
}
