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
        Schema::create('operation_monthly_sc_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan');
            $table->unsignedBigInteger('month_id');
            $table->foreign('month_id')->references('id')->on('month_records')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_monthly_sc_plans');
    }
};
