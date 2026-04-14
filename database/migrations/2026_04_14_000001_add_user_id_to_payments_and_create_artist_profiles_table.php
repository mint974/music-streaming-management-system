<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payments', 'user_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('payments', 'subscription_id')) {
            DB::statement("\n                update payments\n                set user_id = (\n                    select subscriptions.user_id\n                    from subscriptions\n                    where subscriptions.id = payments.subscription_id\n                )\n                where user_id is null\n            ");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'artist_verified_at',
                'artist_revoked_at',
                'artist_name',
                'bio',
                'cover_image',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('artist_verified_at')->nullable()->after('artist_revoked_at');
            $table->timestamp('artist_revoked_at')->nullable()->after('artist_verified_at');
            $table->string('artist_name', 100)->nullable()->after('lock_reason');
            $table->text('bio')->nullable()->after('artist_name');
            $table->string('cover_image')->nullable()->after('bio');
        });
    }
};