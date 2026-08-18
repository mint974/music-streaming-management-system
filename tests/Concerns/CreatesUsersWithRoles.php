<?php

namespace Tests\Concerns;

use App\Models\Role;
use App\Models\User;

trait CreatesUsersWithRoles
{
    protected function createUserWithRole(string $role = 'free', array $attributes = []): User
    {
        $defaults = [
            'name' => 'Test User',
            'email' => 'user_'.uniqid().'@example.com',
            'password' => 'password',
            'status' => 'Đang hoạt động',
            'deleted' => false,
            'is_onboarded' => true,
        ];

        foreach (['admin', 'free', 'premium', 'artist'] as $roleSlug) {
            Role::query()->firstOrCreate(
                ['slug' => $roleSlug],
                ['name' => ucfirst($roleSlug), 'description' => 'Role fixture for automated testing']
            );
        }

        $user = User::query()->create(array_merge($defaults, $attributes));
        $user->assignRole($role);

        return $user;
    }
}
