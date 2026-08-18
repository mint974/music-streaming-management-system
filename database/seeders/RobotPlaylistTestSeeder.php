<?php

namespace Database\Seeders;

use App\Models\Playlist;
use App\Models\Role;
use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class RobotPlaylistTestSeeder extends Seeder
{
    public function run(): void
    {
        $database = DB::connection()->getDatabaseName();
        if (app()->environment() !== 'testing' || $database !== 'wavex_music_testing') {
            throw new RuntimeException('RobotPlaylistTestSeeder may only run against wavex_music_testing.');
        }

        $email = trim((string) env('ROBOT_PREMIUM_EMAIL'));
        $emptyEmail = trim((string) env('ROBOT_PREMIUM_EMPTY_EMAIL'));
        $password = (string) env('ROBOT_PREMIUM_PASSWORD');
        if ($email === '' || $emptyEmail === '' || $password === '') {
            throw new RuntimeException('ROBOT_PREMIUM_EMAIL, ROBOT_PREMIUM_EMPTY_EMAIL and ROBOT_PREMIUM_PASSWORD are required.');
        }

        $this->call(RobotSearchTestSeeder::class);

        DB::transaction(function () use ($email, $emptyEmail, $password): void {
            $role = Role::query()->firstOrCreate(
                ['slug' => 'premium'],
                ['name' => 'Thính giả Premium', 'description' => 'Tài khoản có quyền Premium']
            );

            $users = collect([$email, $emptyEmail])->map(function (string $userEmail, int $index) use ($password, $role): User {
                $user = User::query()->updateOrCreate(
                    ['email' => $userEmail],
                    [
                        'name' => $index === 0 ? 'Robot Premium User' : 'Robot Empty Playlist User',
                        'password' => Hash::make($password),
                        'status' => 'Đang hoạt động',
                        'deleted' => false,
                        'is_onboarded' => true,
                        'email_verified_at' => now(),
                    ]
                );
                $user->syncRoles([$role->slug]);
                return $user;
            });

            $mainUser = $users->first();
            Playlist::query()->whereIn('user_id', $users->pluck('id'))->delete();

            foreach ([
                'Robot Playlist Duplicate',
                'Robot Playlist Open',
                'Robot Playlist Rename',
                'Robot Playlist Add Song',
                'Robot Playlist Remove Song',
            ] as $name) {
                $mainUser->playlists()->create(['name' => $name, 'description' => "Fixture for {$name}"]);
            }

            $song = Song::query()->where('title', 'Robot Search Song Alpha')->firstOrFail();
            $removePlaylist = $mainUser->playlists()->where('name', 'Robot Playlist Remove Song')->firstOrFail();
            $removePlaylist->songs()->attach($song->id, ['sort_order' => 1]);
        });

        $this->command?->info("Robot playlist data prepared in {$database}.");
    }
}
