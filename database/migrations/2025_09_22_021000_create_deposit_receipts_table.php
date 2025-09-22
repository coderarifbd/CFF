<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_receipts', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->decimal('total_amount', 14, 2);
            $table->enum('payment_method', ['cash','bank','mobile']);
            $table->text('note')->nullable();
            $table->foreignId('added_by')->constrained('users');
            $table->timestamps();

            $table->index(['member_id','date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_receipts');
    }
};
