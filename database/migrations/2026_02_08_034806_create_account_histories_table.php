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
            $table->string('type', 20)->default('history');
            $table->text('action');
            $table->enum('status', ['Đang hoạt động', 'Bị khóa', 'Bị vô hiệu hóa', 'Đang yêu cầu khôi phục'])->default('Đang hoạt động');
            $table->text('lock_reason')->nullable();
            $table->text('content');
            $table->enum('unlock_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->foreignId('user_id')
                ->constrained(table: 'users', column: 'id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('created_by')
                ->constrained(table: 'users', column: 'id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamps();

            $table->index(['type', 'unlock_status'], 'idx_type_unlock_status');
            $table->foreign('handled_by')->references('id')->on('users')->nullOnDelete();
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
