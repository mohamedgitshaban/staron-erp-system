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
        Schema::table('chart_account_validations', function (Blueprint $table) {
            $table->json('hierarchy')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_account_validations', function (Blueprint $table) {
            $table->text('hierarchy')->nullable()->change();
        });
    }
};
