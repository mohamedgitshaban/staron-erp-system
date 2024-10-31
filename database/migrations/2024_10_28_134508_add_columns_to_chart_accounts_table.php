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
        Schema::table('chart_accounts', function (Blueprint $table) {
            $table->text("account_description")->nullable()->after("parent_id");
            $table->string("account_lineage")->nullable()->after("account_description");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_accounts', function (Blueprint $table) {
            $table->dropColumn("account_description");
            $table->dropColumn("account_lineage");
        });
    }
};
