<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('image_path');
            $table->string('target_url');
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            // Lên lịch hiển thị (có thể null nếu hiển thị mãi mãi)
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            
            $table->integer('order_index')->default(0); 
            $table->unsignedBigInteger('clicks')->default(0); // Lượt click
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
