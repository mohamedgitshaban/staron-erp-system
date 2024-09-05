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
        // we create this migration to reffrance the tables of orgnization panal like(payroll, insurance,rents)
        Schema::table('chart_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger("reffrence")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_accounts', function (Blueprint $table) {
            //
        });
    }
};
