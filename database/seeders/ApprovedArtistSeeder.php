<?php

namespace Database\Seeders;

use App\Models\AccountHistory;
use App\Models\ArtistPackage;
use App\Models\ArtistProfile;
use App\Models\ArtistRegistration;
use App\Models\Payment;
use App\Models\User;
use App\Models\Album;
use App\Models\Genre;
use App\Models\Song;
use App\Models\SongLyric;
use App\Models\SongLyricLine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ApprovedArtistSeeder extends Seeder
{
    /**
     * Seed 1 artist account with full approval lifecycle:
     * account created -> artist registration submitted -> payment done -> admin approved.
     */
    public function run(): void
    {
        $now = now();

        /** @var \App\Models\User|null $admin */
        $admin = User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'admin'))
            ->where('deleted', false)
            ->orderBy('id')
            ->first();

        $artist = User::updateOrCreate(
            ['email' => 'artist.seed@bluewavemusic.com'],
            [
                'name' => 'Huy Hoang',
                'password' => Hash::make('Az@12345'),
                'phone' => '0988001122',
                'birthday' => '2000-10-15',
                'gender' => 'Nam',
                'status' => 'Đang hoạt động',
                'deleted' => false,
                'email_verified_at' => $now,
                'avatar' => '/storage/avt.jpg',
            ]
        );

        $artist->syncRoles(['artist']);

        $package = ArtistPackage::query()->where('is_active', true)->orderByDesc('price')->first();
        if (! $package) {
            $package = ArtistPackage::create([
                'name' => 'Goi Seed Artist',
                'description' => 'Seed package for artist lifecycle.',
                'price' => 249000,
                'duration_days' => 365,
                'is_active' => true,
            ]);
        }

        $paidAt = Carbon::now('Asia/Ho_Chi_Minh')->subDays(6);
        $reviewedAt = Carbon::now('Asia/Ho_Chi_Minh')->subDays(4);

        $artistProfile = ArtistProfile::updateOrCreate(
            ['user_id' => $artist->id],
            [
                'artist_package_id' => $package->id,
                'stage_name' => 'HH Beats',
                'bio' => 'Independent artist seeded for full artist registration lifecycle.',
                'avatar' => $artist->avatar,
                'cover_image' => null,
                'verified_at' => $reviewedAt,
                'status' => \App\Models\ArtistProfile::STATUS_ACTIVE,
                'revoked_at' => null,
                'start_date' => $reviewedAt,
                'end_date' => $reviewedAt->copy()->addDays((int) ($package->duration_days ?? 365)),
            ]
        );

        $registration = ArtistRegistration::updateOrCreate(
            [
                'user_id' => $artist->id,
                'package_id' => $package->id,
                'submitted_stage_name' => $artist->artist_name ?? $artist->name,
            ],
            [
                'submitted_avt' => $artist->artistProfile?->avatar ?? $artist->avatar,
                'submitted_cover_image' => $artist->artistProfile?->cover_image,
                'status' => 'approved',
                'admin_note' => 'Hồ sơ hợp lệ, duyệt tài khoản nghệ sĩ seed.',
                'reviewed_by' => optional($admin)->id,
                'reviewed_at' => $reviewedAt,
                'approved_at' => $reviewedAt,
                'rejected_at' => null,
                'rejection_reason' => null,
                'expires_at' => $reviewedAt->copy()->addDays((int) ($package->duration_days ?? 365)),
            ]
        );

        Payment::updateOrCreate(
            [
                'payable_type' => ArtistRegistration::class,
                'payable_id' => $registration->id,
            ],
            [
                'user_id' => $artist->id,
                'provider' => 'VNPAY',
                'method' => 'VNPAY',
                'amount' => $package->price,
                'status' => 'paid',
                'transaction_code' => 'ART-SEED-APPROVED-001',
                'provider_transaction_no' => 'VNPSEED000001',
                'provider_pay_date' => $paidAt->format('YmdHis'),
                'paid_at' => $paidAt,
                'raw_response' => [
                    'seed' => true,
                    'source' => 'ApprovedArtistSeeder',
                ],
                'refund_amount' => null,
                'refunded_at' => null,
            ]
        );

        AccountHistory::query()
            ->where('user_id', $artist->id)
            ->whereIn('action', [
                'Đăng ký tài khoản mới',
                'Gửi đăng ký trở thành Nghệ sĩ',
                'Thanh toán gói đăng ký Nghệ sĩ thành công',
                '[Admin] Phê duyệt đăng ký Nghệ sĩ — ' . ($artist->artist_name ?? $artist->name),
                '[Hệ thống] Nâng cấp vai trò tài khoản thành Nghệ sĩ',
            ])
            ->delete();

        $historyRows = [
            [
                'action' => 'Đăng ký tài khoản mới',
                'created_by' => $artist->id,
                'created_at' => $paidAt->copy()->subDays(2),
            ],
            [
                'action' => 'Gửi đăng ký trở thành Nghệ sĩ',
                'created_by' => $artist->id,
                'created_at' => $paidAt->copy()->subHours(3),
            ],
            [
                'action' => 'Thanh toán gói đăng ký Nghệ sĩ thành công',
                'created_by' => $artist->id,
                'created_at' => $paidAt,
            ],
            [
                'action' => '[Admin] Phê duyệt đăng ký Nghệ sĩ — ' . ($artist->artist_name ?? $artist->name),
                'created_by' => $admin?->id ?? $artist->id,
                'created_at' => $reviewedAt,
            ],
            [
                'action' => '[Hệ thống] Nâng cấp vai trò tài khoản thành Nghệ sĩ',
                'created_by' => $admin?->id ?? $artist->id,
                'created_at' => $reviewedAt->copy()->addMinute(),
            ],
        ];

        foreach ($historyRows as $row) {
            AccountHistory::create([
                'type' => 'history',
                'action' => $row['action'],
                'status' => 'Đang hoạt động',
                'lock_reason' => null,
                'content' => null,
                'unlock_status' => null,
                'admin_note' => null,
                'handled_by' => null,
                'handled_at' => null,
                'user_id' => $artist->id,
                'created_by' => $row['created_by'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['created_at'],
            ]);
        }

        // --- Seed 1 Album and 14 Songs for Huy Hoang ---
        $genre = Genre::firstOrCreate(['name' => 'Pop'], ['slug' => 'pop']);

        $album = Album::updateOrCreate(
            ['title' => 'The Hits Collection', 'artist_profile_id' => $artistProfile->id],
            [
                'description' => 'A collection of the greatest hits from Huy Hoang.',
                'cover_image' => 'covers/albums/default.jpg',
                'released_date' => $now,
                'status' => 'published',
                'deleted' => false,
            ]
        );

        $this->command->info('Seeding 14 target songs...');

        for ($i = 1; $i <= 14; $i++) {
            $prefix = sprintf('A%02d', $i);
            
            // Find mp3
            $mp3Files = glob(storage_path('app/public/songs/custom/' . $prefix . '*.mp3'));
            if (empty($mp3Files)) {
                $mp3Files = glob(storage_path('app/public/songs/custom/' . $prefix . '*.[mM][pP]3'));
                if (empty($mp3Files)) {
                    $this->command->warn("Missing mp3 file for $prefix");
                    continue;
                }
            }

            $mp3Path = $mp3Files[0];
            $mp3Filename = basename($mp3Path);
            
            // Title parsing
            $search = [$prefix . '_', '.mp3', '.MP3', '_'];
            $replace = ['', '', '', ' '];
            $title = trim(str_replace($search, $replace, $mp3Filename));

            // Cover parsing
            $coverPaths = glob(storage_path('app/public/covers/songs/custom/' . $prefix . '*.*'));
            $coverUrl = !empty($coverPaths) ? 'covers/songs/custom/' . basename($coverPaths[0]) : null;

            // Lyrics parsing (.lrc or .rlc)
            $lyricPaths = glob(storage_path('app/public/lyrics/' . $prefix . '*.lrc'));
            if (empty($lyricPaths)) {
                $lyricPaths = glob(storage_path('app/public/lyrics/' . $prefix . '*.rlc'));
            }
            $rawLyrics = !empty($lyricPaths) ? file_get_contents($lyricPaths[0]) : '';

            $listens = random_int(5000000, 15000000);
            $duration = random_int(180, 240);
            
            $song = Song::updateOrCreate(
                ['title' => $title, 'artist_profile_id' => $artistProfile->id],
                [
                    'genre_id' => $genre->id,
                    'album_id' => $album->id,
                    'duration' => $duration,
                    'file_path' => 'songs/custom/' . $mp3Filename,
                    'file_mime' => 'audio/mpeg',
                    'file_size' => filesize($mp3Path),
                    'cover_image' => $coverUrl,
                    'released_date' => $now,
                    'publish_at' => $now,
                    'status' => 'published',
                    'listens' => $listens,
                    'deleted' => false,
                ]
            );

            if (!empty($rawLyrics)) {
                $songLyric = SongLyric::firstOrCreate(
                    [
                        'song_id' => $song->id,
                        'source' => 'admin',
                    ],
                    [
                        'name' => 'Lời đồng bộ #1',
                        'language_code' => 'vi',
                        'source' => 'admin',
                        'is_default' => true,
                        'is_visible' => true,
                    ]
                );

                // Backfill dữ liệu cho bản ghi lyric cũ đã tồn tại từ các lần seed trước.
                $songLyric->update([
                    'name' => $songLyric->name ?: 'Lời đồng bộ #1',
                    'is_default' => true,
                    'is_visible' => true,
                ]);

                SongLyric::where('song_id', $song->id)
                    ->where('id', '!=', $songLyric->id)
                    ->update(['is_default' => false]);

                // Create lines
                $lines = explode("\n", $rawLyrics);
                $order = 0;
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (preg_match('/^\[(\d{2}):(\d{2})\.(\d{2,3})\](.*)/', $line, $matches)) {
                        $min = (int)$matches[1];
                        $sec = (int)$matches[2];
                        $ms = (int)str_pad($matches[3], 3, '0', STR_PAD_RIGHT);
                        $text = trim($matches[4]);
                        $totalMs = ($min * 60 * 1000) + ($sec * 1000) + $ms;
                        
                        SongLyricLine::updateOrCreate(
                            [
                                'song_lyric_id' => $songLyric->id,
                                'start_time_ms' => $totalMs,
                            ],
                            [
                                'content' => $text,
                                'line_order' => $order++,
                            ]
                        );
                    } elseif (preg_match('/^\[(\d{2}):(\d{2})\](.*)/', $line, $matches2)) {
                        $min = (int)$matches2[1];
                        $sec = (int)$matches2[2];
                        $text = trim($matches2[3]);
                        $totalMs = ($min * 60 * 1000) + ($sec * 1000);

                        SongLyricLine::updateOrCreate(
                            [
                                'song_lyric_id' => $songLyric->id,
                                'start_time_ms' => $totalMs,
                            ],
                            [
                                'content' => $text,
                                'line_order' => $order++,
                            ]
                        );
                    }
                }
            }
        }

        $this->command->info('ApprovedArtistSeeder: seeded artist account artist.seed@bluewavemusic.com | password: Aa@12345');
    }
}
