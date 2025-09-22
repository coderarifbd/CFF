<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conditionally drop indexes that reference year_month using raw SQL
        try {
            $idx = collect(DB::select("SHOW INDEX FROM `deposits` WHERE Key_name = 'uniq_member_month_type'"));
            if ($idx->isNotEmpty()) {
                DB::statement("ALTER TABLE `deposits` DROP INDEX `uniq_member_month_type`");
            }
        } catch (\Throwable $e) { /* ignore */ }

        try {
            $idx = collect(DB::select("SHOW INDEX FROM `deposits` WHERE Key_name = 'idx_member_type_month'"));
            if ($idx->isNotEmpty()) {
                DB::statement("ALTER TABLE `deposits` DROP INDEX `idx_member_type_month`");
            }
        } catch (\Throwable $e) { /* ignore */ }

        // Drop the year_month column if present (without requiring doctrine/dbal)
        try {
            // Some environments may not allow hasColumn without dbal; do raw check
            $hasColumn = collect(DB::select("SHOW COLUMNS FROM `deposits` LIKE 'year_month'"))->isNotEmpty();
            if ($hasColumn) {
                Schema::table('deposits', function (Blueprint $table) {
                    $table->dropColumn('year_month');
                });
            }
        } catch (\Throwable $e) {
            // ignore if cannot determine/drop
        }

        // Add safer index using date instead of generated year_month (only if missing)
        try {
            $idx = collect(DB::select("SHOW INDEX FROM `deposits` WHERE Key_name = 'idx_member_type_date'"));
            if ($idx->isEmpty()) {
                DB::statement("ALTER TABLE `deposits` ADD INDEX `idx_member_type_date` (`member_id`,`type`,`date`)");
            }
        } catch (\Throwable $e) { /* ignore */ }
    }

    public function down(): void
    {
        // Drop date-based index if present
        try {
            $idx = collect(DB::select("SHOW INDEX FROM `deposits` WHERE Key_name = 'idx_member_type_date'"));
            if ($idx->isNotEmpty()) {
                DB::statement("ALTER TABLE `deposits` DROP INDEX `idx_member_type_date`");
            }
        } catch (\Throwable $e) { /* ignore */ }

        // Recreate a simple varchar column (not generated) for backwards compatibility
        try {
            $cols = collect(DB::select("SHOW COLUMNS FROM `deposits` LIKE 'year_month'"));
            if ($cols->isEmpty()) {
                DB::statement("ALTER TABLE `deposits` ADD COLUMN `year_month` varchar(7) NULL AFTER `added_by`");
            }
        } catch (\Throwable $e) { /* ignore */ }

        // Optionally recreate the non-unique index if needed
        try {
            $idx = collect(DB::select("SHOW INDEX FROM `deposits` WHERE Key_name = 'idx_member_type_month'"));
            if ($idx->isEmpty()) {
                DB::statement("ALTER TABLE `deposits` ADD INDEX `idx_member_type_month` (`member_id`,`type`,`year_month`)");
            }
        } catch (\Throwable $e) { /* ignore */ }
    }
};
