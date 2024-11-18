<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginatingModuleToMainJournalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('main_journals', function (Blueprint $table) {
            $table->string('originating_module')->nullable()->after('description'); // Assuming 'description' is the last column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('main_journals', function (Blueprint $table) {
            $table->dropColumn('originating_module');
        });
    }
}
