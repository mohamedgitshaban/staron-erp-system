<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToChartAccountValidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chart_account_validations', function (Blueprint $table) {
            // Add debit_validation_id and credit_validation_id if they do not exist
            if (!Schema::hasColumn('chart_account_validations', 'debit_validation_id')) {
                $table->unsignedBigInteger('debit_validation_id')->nullable()->after('id');
                $table->foreign('debit_validation_id')->references('id')->on('chart_account_validations')->onDelete('cascade');
            }

            if (!Schema::hasColumn('chart_account_validations', 'credit_validation_id')) {
                $table->unsignedBigInteger('credit_validation_id')->nullable()->after('debit_validation_id');
                $table->foreign('credit_validation_id')->references('id')->on('chart_account_validations')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chart_account_validations', function (Blueprint $table) {
            if (Schema::hasColumn('chart_account_validations', 'debit_validation_id')) {
                $table->dropForeign(['debit_validation_id']);
                $table->dropColumn('debit_validation_id');
            }

            if (Schema::hasColumn('chart_account_validations', 'credit_validation_id')) {
                $table->dropForeign(['credit_validation_id']);
                $table->dropColumn('credit_validation_id');
            }
        });
    }
}
