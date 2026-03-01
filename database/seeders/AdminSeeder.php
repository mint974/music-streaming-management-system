<?php

namespace Database\Seeders;

use App\Models\AccountHistory;
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
        $user = User::updateOrCreate(
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

        if ($user->wasRecentlyCreated) {
            AccountHistory::create([
                'type'       => 'history',
                'action'     => '[Hệ thống] Tạo tài khoản Admin',
                'status'     => 'Đang hoạt động',
                'user_id'    => $user->id,
                'created_by' => $user->id,
            ]);
        }

        $this->command->info('Admin account created/updated — email: admin@bluewavemusic.com | password: Aa@12345');
    }
}
