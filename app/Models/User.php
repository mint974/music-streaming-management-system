<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
        'deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date',
        'deleted' => 'boolean',
        'password' => 'hashed',
    ];

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
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is singer.
     */
    public function isSinger(): bool
    {
        return $this->role === 'singer';
    }
}
