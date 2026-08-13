<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class RobotTestUserSeeder extends Seeder
{
    public function run(): void
    {
        $database = DB::connection()->getDatabaseName();

        if (app()->environment() !== 'testing' || $database !== 'wavex_music_testing') {
            throw new RuntimeException('RobotTestUserSeeder may only run against wavex_music_testing.');
        }

        $email = trim((string) env('ROBOT_TEST_EMAIL'));
        $lockedEmail = trim((string) env('ROBOT_LOCKED_EMAIL'));
        $password = (string) env('ROBOT_TEST_PASSWORD');

        if ($email === '' || $lockedEmail === '' || $password === '') {
            throw new RuntimeException('ROBOT_TEST_EMAIL, ROBOT_LOCKED_EMAIL and ROBOT_TEST_PASSWORD are required.');
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Robot Login Test User',
                'password' => Hash::make($password),
                'status' => 'Đang hoạt động',
                'deleted' => false,
                'is_onboarded' => true,
                'email_verified_at' => now(),
            ]
        );

        Role::query()->firstOrCreate(
            ['slug' => 'free'],
            ['name' => 'Thính giả Free', 'description' => 'Tài khoản nghe nhạc miễn phí']
        );
        $user->syncRoles(['free']);

        $lockedUser = User::query()->updateOrCreate(
            ['email' => $lockedEmail],
            [
                'name' => 'Robot Locked Login Test User',
                'password' => Hash::make($password),
                'status' => 'Bị khóa',
                'deleted' => false,
                'is_onboarded' => true,
                'email_verified_at' => now(),
            ]
        );
        $lockedUser->syncRoles(['free']);

        $this->command?->info("Robot test user prepared in {$database}.");
    }
}
