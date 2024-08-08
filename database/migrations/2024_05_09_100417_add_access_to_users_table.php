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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('hraccess')->default(false);
            $table->boolean('salesaccess')->default(false);
            $table->boolean('technicalaccess')->default(false);
            $table->boolean('controlaccess')->default(false);
            $table->boolean('supplychainaccess')->default(false);
            $table->boolean('operationaccess')->default(false);
            $table->boolean('financeaccess')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
