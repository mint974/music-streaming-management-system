<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultRoleSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     */
    public function run(): void
    {
        $now = now();
        $roleRows = [
            ['slug' => 'admin', 'name' => 'Quản trị viên', 'description' => 'Quyền quản trị toàn hệ thống', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'free', 'name' => 'Thính giả Free', 'description' => 'Tài khoản nghe nhạc miễn phí', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'premium', 'name' => 'Thính giả Premium', 'description' => 'Tài khoản có quyền Premium', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'artist', 'name' => 'Nghệ sĩ', 'description' => 'Tài khoản nghệ sĩ', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($roleRows as $row) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $row['slug']],
                $row
            );
        }

        $this->command->info('Roles seeded successfully.');
    }
}
