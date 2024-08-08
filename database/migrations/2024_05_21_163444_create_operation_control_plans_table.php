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
        Schema::create('operation_control_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan');
            $table->unsignedBigInteger('week_records_id');
            $table->foreign('week_records_id')->references('id')->on('week_records')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_control_plans');
    }
};
