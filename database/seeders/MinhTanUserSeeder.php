<?php

namespace Database\Seeders;

use App\Models\AccountHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MinhTanUserSeeder extends Seeder
{
    /**
     * Seed tài khoản Minh Tân.
     * Email: minhtan090704@gmail.com | Password: Ab@12345
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'minhtan090704@gmail.com'],
            [
                'name'              => 'minh tân',
                'password'          => Hash::make('Az@12345'),
                'phone'             => '0775097409',
                'birthday'          => '2010-07-09',
                'gender'            => 'Nam',

                'status'            => 'Đang hoạt động',
                'deleted'           => false,
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles(['free']);

        if ($user->wasRecentlyCreated) {
            AccountHistory::create([
                'type'       => 'history',
                'action'     => 'Đăng ký tài khoản mới',
                'status'     => 'Đang hoạt động',
                'user_id'    => $user->id,
                'created_by' => $user->id,
            ]);
        }

        $this->command->info('User account created/updated — email: minhtan090704@gmail.com | password: Ab@12345');
    }
}
