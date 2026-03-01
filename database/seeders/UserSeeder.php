<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the default user account.
     * Default password: Aa@12345
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'user@bluewavemusic.com'],
            [
                'name'              => 'Người dùng Demo',
                'password'          => Hash::make('Aa@12345'),
                'role'              => 'free',
                'status'            => 'Đang hoạt động',
                'deleted'           => false,
                'email_verified_at' => now(),
                'avatar'            => '/storage/avt.jpg',
            ]
        );

        $this->command->info('User account created/updated — email: user@bluewavemusic.com | password: Aa@12345');
    }
}
