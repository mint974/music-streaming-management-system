<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename roles to match the permission system:
     *   old 'singer' → 'artist'  (Nghệ sĩ)
     *   old 'user'   → 'free'    (Thính giả miễn phí)
     * Add new:       'premium'  (Thính giả Premium)
     */
    public function up(): void
    {
        // Step 1: Widen enum to include both old and new values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','artist','free','premium','user','singer') NOT NULL DEFAULT 'free'");

        // Step 2: Migrate existing data
        DB::table('users')->where('role', 'singer')->update(['role' => 'artist']);
        DB::table('users')->where('role', 'user')->update(['role' => 'free']);

        // Step 3: Narrow enum to final values only
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','artist','free','premium') NOT NULL DEFAULT 'free'");
    }

    public function down(): void
    {
        // Step 1: Widen enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','artist','free','premium','user','singer') NOT NULL DEFAULT 'user'");

        // Step 2: Reverse data migration
        DB::table('users')->where('role', 'artist')->update(['role' => 'singer']);
        DB::table('users')->where('role', 'free')->update(['role' => 'user']);
        DB::table('users')->where('role', 'premium')->update(['role' => 'user']);

        // Step 3: Narrow enum back
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','user','singer') NOT NULL DEFAULT 'user'");
    }
};
