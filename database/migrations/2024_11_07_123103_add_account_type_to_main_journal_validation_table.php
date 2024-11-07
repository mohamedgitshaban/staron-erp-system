<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountTypeToMainJournalValidationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('main_journal_validations', function (Blueprint $table) {
            $table->string('account_type')->nullable()->after('originating_module'); // Replace 'existing_column' with the last column name in this table
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
            $table->dropColumn('account_type');
        });
    }
}
