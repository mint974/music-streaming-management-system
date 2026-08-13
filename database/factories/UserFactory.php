<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => 'Đang hoạt động',
            'deleted' => false,
            'is_onboarded' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /** Create an artist (Nghệ sĩ) user. */
    public function artist(): static
    {
        return $this->withRole('artist');
    }

    /** Create a premium listener (Thính giả Premium). */
    public function premium(): static
    {
        return $this->withRole('premium');
    }

    /** Create an admin user. */
    public function admin(): static
    {
        return $this->withRole('admin');
    }

    private function withRole(string $slug): static
    {
        return $this->afterCreating(function (User $user) use ($slug): void {
            Role::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => ucfirst($slug), 'description' => 'Role fixture for automated testing']
            );

            $user->syncRoles([$slug]);
        });
    }
}
