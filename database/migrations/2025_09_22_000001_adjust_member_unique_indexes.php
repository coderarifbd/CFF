<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Drop existing unique constraints on single columns
            // Use explicit names as per MySQL error and Laravel's default naming
            try { $table->dropUnique('members_phone_unique'); } catch (\Throwable $e) {}
            try { $table->dropUnique('members_nid_unique'); } catch (\Throwable $e) {}

            // Recreate as composite unique with deleted_at so duplicates are allowed when previous rows are soft-deleted
            $table->unique(['phone','deleted_at'], 'members_phone_deleted_at_unique');
            $table->unique(['nid','deleted_at'], 'members_nid_deleted_at_unique');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Drop composite unique indexes
            try { $table->dropUnique('members_phone_deleted_at_unique'); } catch (\Throwable $e) {}
            try { $table->dropUnique('members_nid_deleted_at_unique'); } catch (\Throwable $e) {}

            // Restore original single-column unique indexes
            $table->unique('phone', 'members_phone_unique');
            $table->unique('nid', 'members_nid_unique');
        });
    }
};
