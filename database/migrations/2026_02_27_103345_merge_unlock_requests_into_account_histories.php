<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Thêm các cột mới vào account_histories (idempotent)
        Schema::table('account_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('account_histories', 'type')) {
                $table->string('type', 20)->default('history')->after('id');
            }
            if (!Schema::hasColumn('account_histories', 'content')) {
                $table->text('content')->nullable()->after('lock_reason');
            }
            if (!Schema::hasColumn('account_histories', 'unlock_status')) {
                $table->enum('unlock_status', ['pending', 'approved', 'rejected'])->nullable()->after('content');
            }
            if (!Schema::hasColumn('account_histories', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('unlock_status');
            }
            if (!Schema::hasColumn('account_histories', 'handled_by')) {
                $table->unsignedBigInteger('handled_by')->nullable()->after('admin_note');
            }
            if (!Schema::hasColumn('account_histories', 'handled_at')) {
                $table->timestamp('handled_at')->nullable()->after('handled_by');
            }
        });

        // Thêm index và foreign key nếu chưa có
        $indexes = collect(DB::select("SHOW INDEX FROM account_histories"))
            ->pluck('Key_name')->toArray();
        $fks = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account_histories' AND CONSTRAINT_TYPE = 'FOREIGN KEY'"))
            ->pluck('CONSTRAINT_NAME')->toArray();

        Schema::table('account_histories', function (Blueprint $table) use ($indexes, $fks) {
            if (!in_array('idx_type_unlock_status', $indexes)) {
                $table->index(['type', 'unlock_status'], 'idx_type_unlock_status');
            }
            if (!in_array('account_histories_handled_by_foreign', $fks)) {
                $table->foreign('handled_by')->references('id')->on('users')->nullOnDelete();
            }
        });

        // 2. Migrate dữ liệu từ unlock_requests → account_histories
        if (Schema::hasTable('unlock_requests')) {
            $rows = DB::table('unlock_requests')->get();
            foreach ($rows as $req) {
                DB::table('account_histories')->insert([
                    'type'          => 'unlock_request',
                    'action'        => 'Gửi yêu cầu mở khóa tài khoản',
                    'status'        => 'Đang yêu cầu khôi phục',
                    'content'       => $req->content,
                    'unlock_status' => $req->status,
                    'admin_note'    => $req->admin_note,
                    'handled_by'    => $req->handled_by,
                    'handled_at'    => $req->handled_at,
                    'user_id'       => $req->user_id,
                    'created_by'    => $req->user_id,
                    'created_at'    => $req->created_at,
                    'updated_at'    => $req->updated_at,
                ]);
            }
            Schema::dropIfExists('unlock_requests');
        }
    }

    public function down(): void
    {
        Schema::create('unlock_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        $rows = DB::table('account_histories')->where('type', 'unlock_request')->get();
        foreach ($rows as $row) {
            DB::table('unlock_requests')->insert([
                'user_id'    => $row->user_id,
                'content'    => $row->content,
                'status'     => $row->unlock_status ?? 'pending',
                'admin_note' => $row->admin_note,
                'handled_by' => $row->handled_by,
                'handled_at' => $row->handled_at,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        DB::table('account_histories')->where('type', 'unlock_request')->delete();

        Schema::table('account_histories', function (Blueprint $table) {
            $table->dropForeign(['handled_by']);
            $table->dropIndex('idx_type_unlock_status');
            $table->dropColumn(['type', 'content', 'unlock_status', 'admin_note', 'handled_by', 'handled_at']);
        });
    }
};
