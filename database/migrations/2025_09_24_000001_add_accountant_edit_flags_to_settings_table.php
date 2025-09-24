<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('allow_accountant_edit_deposits')->default(false);
            $table->boolean('allow_accountant_edit_expenses')->default(false);
            $table->boolean('allow_accountant_edit_other_income')->default(false);
            $table->boolean('allow_accountant_edit_investment_interest')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'allow_accountant_edit_deposits',
                'allow_accountant_edit_expenses',
                'allow_accountant_edit_other_income',
                'allow_accountant_edit_investment_interest',
            ]);
        });
    }
};
