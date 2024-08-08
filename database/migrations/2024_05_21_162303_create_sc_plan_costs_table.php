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
        Schema::create('sc_plan_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('control_sc_plan_id');
            $table->foreign('control_sc_plan_id')->references('id')->on('control_sc_plans')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_plan_costs');
    }
};
