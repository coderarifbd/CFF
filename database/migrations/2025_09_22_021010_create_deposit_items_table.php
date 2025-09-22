<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('deposit_receipts')->cascadeOnDelete();
            $table->enum('type', ['subscription','extra','fine']);
            $table->decimal('amount', 14, 2);
            $table->timestamps();

            $table->index(['receipt_id','type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_items');
    }
};
