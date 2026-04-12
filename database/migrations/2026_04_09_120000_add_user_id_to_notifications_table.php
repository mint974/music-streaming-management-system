<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('notifications', 'user_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('type')->constrained('users')->nullOnDelete();
                $table->index(['user_id', 'read_at']);
            });
        }

        // Backfill existing notification records for user notifiable type.
        DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->whereNull('user_id')
            ->update(['user_id' => DB::raw('notifiable_id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('notifications', 'user_id')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_id_read_at_index');
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
