<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            // Drop the unique constraint that enforces uniqueness across all types
            // so that only application-level logic prevents duplicates for subscriptions.
            try {
                $table->dropUnique('uniq_member_month_type');
            } catch (\Throwable $e) {
                // index may not exist in some environments; ignore
            }

            // Add a regular index for performance on common filters
            $table->index(['member_id', 'type', 'year_month'], 'idx_member_type_month');
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            // Remove the non-unique index
            try {
                $table->dropIndex('idx_member_type_month');
            } catch (\Throwable $e) {
                // ignore
            }

            // Recreate the original unique index if needed
            $table->unique(['member_id','type','year_month'], 'uniq_member_month_type');
        });
    }
};
