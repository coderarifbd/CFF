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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['land','business','bank','other']);
            $table->decimal('amount', 14, 2);
            $table->date('date');
            $table->string('agreement_document')->nullable(); // stored file path
            $table->text('notes')->nullable();
            $table->enum('status', ['active','returned'])->default('active');
            $table->date('return_date')->nullable();
            $table->decimal('return_amount', 14, 2)->nullable();
            $table->foreignId('added_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
