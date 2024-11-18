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
        $table->dropForeign(['requested_by']);
        $table->dropForeign(['debit_id']);
        $table->dropForeign(['credit_id']);
        $table->dropForeign(['debit_validation_id']);
        $table->dropForeign(['credit_validation_id']);

        $table->unsignedBigInteger('requested_by')->nullable()->change();
        $table->unsignedBigInteger('debit_id')->nullable()->change();
        $table->unsignedBigInteger('credit_id')->nullable()->change();
        $table->unsignedBigInteger('debit_validation_id')->nullable()->change();
        $table->unsignedBigInteger('credit_validation_id')->nullable()->change();

        // Re-add foreign keys
        $table->foreign('requested_by', 'mjv_requested_by_fk')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');

        $table->foreign('debit_id', 'mjv_debit_id_fk')
            ->references('id')
            ->on('chart_accounts')
            ->onDelete('cascade');

        $table->foreign('credit_id', 'mjv_credit_id_fk')
            ->references('id')
            ->on('chart_accounts')
            ->onDelete('cascade');

        $table->foreign('debit_validation_id', 'mjv_debit_validation_id_fk')
            ->references('id')
            ->on('chart_account_validations')
            ->onDelete('cascade');

        $table->foreign('credit_validation_id', 'mjv_credit_validation_id_fk')
            ->references('id')
            ->on('chart_account_validations')
            ->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_journal_validations', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['debit_id']);
            $table->dropForeign(['credit_id']);
            $table->dropForeign(['debit_validation_id']);
            $table->dropForeign(['credit_validation_id']);

        });
    }
};
