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
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('main_journal_validations', 'debit_validation_id')) {
                $table->unsignedBigInteger('debit_validation_id')->nullable()->after('debit_id');
                $table->foreign('debit_validation_id')->references('id')->on('chart_account_validations')->onDelete('cascade');
            }

            if (!Schema::hasColumn('main_journal_validations', 'credit_validation_id')) {
                $table->unsignedBigInteger('credit_validation_id')->nullable()->after('credit_id');
                $table->foreign('credit_validation_id')->references('id')->on('chart_account_validations')->onDelete('cascade');
            }

            // Create the new column 'request_source'
            if (!Schema::hasColumn('main_journal_validations', 'request_source')) {
                $table->string('request_source')->nullable()->after('transaction_type');
            }
        });

        // Copy data from 'originating_module' to 'request_source'
        DB::statement("UPDATE main_journal_validations SET request_source = originating_module");

        // Drop the old 'originating_module' column
        Schema::table('main_journal_validations', function (Blueprint $table) {
            if (Schema::hasColumn('main_journal_validations', 'originating_module')) {
                $table->dropColumn('originating_module');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_journal_validations', function (Blueprint $table) {
            $table->dropForeign(['debit_validation_id']);
            $table->dropForeign(['credit_validation_id']);
            $table->dropColumn(['debit_validation_id', 'credit_validation_id']);

            // Recreate the 'originating_module' column
            $table->string('originating_module')->nullable();

            // Copy data back from 'request_source' to 'originating_module'
            DB::statement("UPDATE main_journal_validations SET originating_module = request_source");

            // Drop the 'request_source' column
            $table->dropColumn('request_source');
        });
    }
};
