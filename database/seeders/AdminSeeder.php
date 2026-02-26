<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the default admin account.
     * Default password: Aa@12345
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@bluewavemusic.com'],
            [
                'name'              => 'Administrator',
                'password'          => Hash::make('Aa@12345'),
                'role'              => 'admin',
                'status'            => 'Đang hoạt động',
                'deleted'           => false,
                'email_verified_at' => now(),
                'avatar'            => '/storage/avt.jpg',
            ]
        );

        $this->command->info('Admin account created/updated — email: admin@bluewavemusic.com | password: Aa@12345');
    }
}
