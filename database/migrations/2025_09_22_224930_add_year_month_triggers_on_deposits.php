<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Ensure normal year_month column exists
        Schema::table('deposits', function (Blueprint $table) {
            // Add column if missing (no DBAL): try-catch raw check
            try {
                $has = collect(DB::select("SHOW COLUMNS FROM `deposits` LIKE 'year_month'"))->isNotEmpty();
                if (!$has) {
                    $table->string('year_month', 7)->nullable()->after('added_by');
                }
            } catch (\Throwable $e) {
                // fallback attempt
                try { $table->string('year_month', 7)->nullable()->after('added_by'); } catch (\Throwable $e2) {}
            }
        });

        // 2) Backfill existing rows
        try {
            DB::statement("UPDATE `deposits` SET `year_month` = DATE_FORMAT(`date`, '%Y-%m') WHERE `year_month` IS NULL");
        } catch (\Throwable $e) {
            // ignore
        }

        // 3) Create composite index (member_id, type, year_month)
        try {
            Schema::table('deposits', function (Blueprint $table) {
                $table->index(['member_id','type','year_month'], 'idx_member_type_month');
            });
        } catch (\Throwable $e) {
            // index may already exist
        }

        // 4) Drop existing triggers if any, then create BEFORE INSERT/UPDATE triggers
        try { DB::unprepared('DROP TRIGGER IF EXISTS `deposits_bi`'); } catch (\Throwable $e) {}
        try { DB::unprepared('DROP TRIGGER IF EXISTS `deposits_bu`'); } catch (\Throwable $e) {}

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `deposits_bi` BEFORE INSERT ON `deposits`
FOR EACH ROW BEGIN
    SET NEW.`year_month` = DATE_FORMAT(NEW.`date`, '%Y-%m');
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `deposits_bu` BEFORE UPDATE ON `deposits`
FOR EACH ROW BEGIN
    SET NEW.`year_month` = DATE_FORMAT(NEW.`date`, '%Y-%m');
END
SQL);
    }

    public function down(): void
    {
        // Drop triggers
        try { DB::unprepared('DROP TRIGGER IF EXISTS `deposits_bi`'); } catch (\Throwable $e) {}
        try { DB::unprepared('DROP TRIGGER IF EXISTS `deposits_bu`'); } catch (\Throwable $e) {}

        // Drop index (keep column for compatibility)
        try {
            Schema::table('deposits', function (Blueprint $table) {
                $table->dropIndex('idx_member_type_month');
            });
        } catch (\Throwable $e) {}
    }
};
