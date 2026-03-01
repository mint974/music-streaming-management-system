<?php

namespace Database\Seeders;

use App\Models\AccountHistory;
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
        $user = User::updateOrCreate(
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

        if ($user->wasRecentlyCreated) {
            AccountHistory::create([
                'type'       => 'history',
                'action'     => 'Đăng ký tài khoản mới',
                'status'     => 'Đang hoạt động',
                'user_id'    => $user->id,
                'created_by' => $user->id,
            ]);
        }

        $this->command->info('User account created/updated — email: user@bluewavemusic.com | password: Aa@12345');
    }
}
