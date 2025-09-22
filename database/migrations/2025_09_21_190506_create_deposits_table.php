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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->enum('type', ['subscription', 'extra', 'fine']);
            $table->decimal('amount', 14, 2);
            $table->enum('payment_method', ['cash', 'bank', 'mobile']);
            $table->text('note')->nullable();
            $table->foreignId('added_by')->constrained('users');
            $table->timestamps();

            // Indexes for common filters
            $table->index(['member_id', 'date']);
            $table->index(['member_id','type','date'], 'idx_member_type_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
