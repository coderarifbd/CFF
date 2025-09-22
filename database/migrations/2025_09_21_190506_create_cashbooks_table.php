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
        Schema::create('cashbooks', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('type', ['income', 'expense']);
            $table->string('category'); // Subscription, Fine, Interest, Other, Investment Outflow, Investment Return
            $table->decimal('amount', 14, 2);
            $table->nullableMorphs('reference'); // reference_type, reference_id (Deposit, Investment, etc)
            $table->text('note')->nullable();
            $table->foreignId('added_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashbooks');
    }
};
