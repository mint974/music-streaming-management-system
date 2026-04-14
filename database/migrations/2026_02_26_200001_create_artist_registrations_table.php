<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('artist_packages');
            $table->string('submitted_stage_name');
            $table->string('submitted_avt')->nullable();
            $table->string('submitted_cover_image')->nullable();

            // Trạng thái đơn đăng ký
            // pending_payment → pending_review → approved | rejected
            $table->string('status')->default('pending_payment');

            // Xét duyệt bởi admin
            $table->text('admin_note')->nullable();
            $table->string('rejection_reason', 50)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Ngày hết hạn quyền nghệ sĩ (set khi admin approve, dựa vào package.duration_days)
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });

        Schema::create('artist_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('artist_package_id')
                ->constrained('artist_packages')
                ->restrictOnDelete();
            $table->string('stage_name');
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover_image')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('status', 50)->default('inactive');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('artist_package_id');
        });

        $activePackageId = DB::table('artist_packages')
            ->where('is_active', true)
            ->orderByDesc('id')
            ->value('id');

        $artistUsers = DB::table('users')
            ->where(function ($query) {
                $query->whereNotNull('users.artist_name')
                    ->orWhereNotNull('users.artist_verified_at')
                    ->orWhereNotNull('users.artist_revoked_at');
            })
            ->select('users.*')
            ->distinct()
            ->get();

        foreach ($artistUsers as $user) {
            $packageId = DB::table('artist_registrations')
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->orderByDesc('id')
                ->value('package_id')
                ?? DB::table('artist_registrations')
                    ->where('user_id', $user->id)
                    ->orderByDesc('id')
                    ->value('package_id')
                ?? $activePackageId;

            if (! $packageId) {
                continue;
            }

            $activeRegistration = DB::table('artist_registrations')
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', now());
                })
                ->orderByDesc('id')
                ->first(['reviewed_at', 'approved_at', 'expires_at']);

            $profileStatus = $user->artist_revoked_at
                ? 'revoked'
                : ($activeRegistration ? 'active' : 'inactive');

            $profileStartDate = $activeRegistration->approved_at
                ?? $activeRegistration->reviewed_at
                ?? null;
            $profileEndDate = $activeRegistration->expires_at
                ?? $user->artist_revoked_at
                ?? null;

            DB::table('artist_profiles')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'artist_package_id' => $packageId,
                    'stage_name' => $user->artist_name ?: $user->name,
                    'bio' => $user->bio,
                    'avatar' => $user->avatar,
                    'cover_image' => $user->cover_image,
                    'verified_at' => $user->artist_verified_at,
                    'status' => $profileStatus,
                    'revoked_at' => $user->artist_revoked_at,
                    'start_date' => $profileStartDate,
                    'end_date' => $profileEndDate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_registrations');
    }
};
