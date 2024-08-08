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
        Schema::table('leaving_balance_logs', function (Blueprint $table) {
            $table->string("type");
            $table->string("requestname");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaving_balance_logs', function (Blueprint $table) {
            //
        });
    }
};
