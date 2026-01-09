<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Performance indexes for frequently queried columns.
     */
    public function up(): void
    {
        // Attendances table indexes
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'idx_attendances_user_date');
            $table->index(['date', 'status'], 'idx_attendances_date_status');
            $table->index('approval_status', 'idx_attendances_approval_status');
        });

        // Activity logs table indexes
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_activity_user_created');
            $table->index('ip_address', 'idx_activity_ip');
            $table->index('action', 'idx_activity_action');
        });

        // Users table indexes for birthday queries
        Schema::table('users', function (Blueprint $table) {
            $table->index('birth_date', 'idx_users_birth_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_user_date');
            $table->dropIndex('idx_attendances_date_status');
            $table->dropIndex('idx_attendances_approval_status');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('idx_activity_user_created');
            $table->dropIndex('idx_activity_ip');
            $table->dropIndex('idx_activity_action');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_birth_date');
        });
    }
};
