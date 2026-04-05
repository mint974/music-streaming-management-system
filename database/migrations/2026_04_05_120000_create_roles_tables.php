<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->string('name', 80);
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('granted_at')->useCurrent();

            $table->primary(['role_id', 'user_id']);
            $table->index('user_id');
        });

        $now = now();
        $roleRows = [
            ['slug' => 'admin', 'name' => 'Quản trị viên', 'description' => 'Quyền quản trị toàn hệ thống', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'free', 'name' => 'Thính giả Free', 'description' => 'Tài khoản nghe nhạc miễn phí', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'premium', 'name' => 'Thính giả Premium', 'description' => 'Tài khoản có quyền Premium', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'artist', 'name' => 'Nghệ sĩ', 'description' => 'Tài khoản nghệ sĩ', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('roles')->insert($roleRows);


    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
