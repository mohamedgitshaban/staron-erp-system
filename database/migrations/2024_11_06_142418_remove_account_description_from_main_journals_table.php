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
        Schema::table('main_journals', function (Blueprint $table) {
            $table->dropColumn(['debit_account_description', 'credit_account_description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_journals', function (Blueprint $table) {
            $table->text(column: "debit_account_description")->nullable();
            $table->text("credit_account_description")->nullable();

        });
    }
};
