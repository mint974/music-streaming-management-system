<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
     Schema::create('account_histories', function (Blueprint $table) {
            $table->id();
            $table->text('action');
            $table->enum('status', ['Đang hoạt động', 'Bị vô hiệu hóa', 'Đang yêu cầu khôi phục'])->default('Đang hoạt động');
            $table->foreignId('user_id')
                ->constrained(
                    table: 'users',
                    column: 'id'
                )
                ->onUpdate('cascade')
                ->onDelete('cascade');


            $table->foreignId('created_by')
                ->constrained(
                    table: 'users',
                    column: 'id'
                )
                ->onUpdate('cascade')
                ->onDelete('cascade');
           $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_histories');
    }
};
