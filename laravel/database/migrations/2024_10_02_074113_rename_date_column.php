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
        Schema::table('incomes', function (Blueprint $table) {
            $table->renameColumn('date', 'income_date');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('date', 'expense_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->renameColumn('income_date', 'date');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('expense_date', 'date');
        });
    }
};
