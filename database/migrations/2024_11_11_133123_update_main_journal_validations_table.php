<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMainJournalValidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('main_journal_validations', function (Blueprint $table) {
            // Make `debit_id` and `credit_id` nullable
            $table->unsignedBigInteger('debit_id')->nullable()->change();
            $table->unsignedBigInteger('credit_id')->nullable()->change();

            // Add `originating_module` column if it doesn't exist
            if (!Schema::hasColumn('main_journal_validations', 'originating_module')) {
                $table->string('originating_module', 255)->nullable()->after('ticket_trail');
            }

            // Ensure foreign key constraints are updated
            $table->dropForeign(['debit_id']);
            $table->dropForeign(['credit_id']);
            $table->foreign('debit_id')->references('id')->on('chart_accounts')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('credit_id')->references('id')->on('chart_accounts')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('main_journal_validations', function (Blueprint $table) {
            // Revert `debit_id` and `credit_id` back to non-nullable
            $table->unsignedBigInteger('debit_id')->nullable(false)->change();
            $table->unsignedBigInteger('credit_id')->nullable(false)->change();

            // Drop `originating_module` column if it exists
            if (Schema::hasColumn('main_journal_validations', 'originating_module')) {
                $table->dropColumn('originating_module');
            }

            // Revert foreign key constraints
            $table->dropForeign(['debit_id']);
            $table->dropForeign(['credit_id']);
            $table->foreign('debit_id')->references('id')->on('chart_accounts')->onDelete('cascade');
            $table->foreign('credit_id')->references('id')->on('chart_accounts')->onDelete('cascade');
        });
    }
}
