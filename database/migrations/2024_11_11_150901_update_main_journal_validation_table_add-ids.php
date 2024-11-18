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
        Schema::table('main_journal_validations', function (Blueprint $table) {
            $table->unsignedBigInteger('debit_validation_id')->nullable()->after('credit_id');
            $table->unsignedBigInteger('credit_validation_id')->nullable()->after('debit_validation_id');
        
            $table->foreign('debit_validation_id')->references('id')->on('chart_account_validations')->onDelete('cascade');
            $table->foreign('credit_validation_id')->references('id')->on('chart_account_validations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_journal_validations', function (Blueprint $table) {
            $table->dropColumn("debit_validation_id");
            $table->dropColumn("credit_validation_id");
            });
    }
};
