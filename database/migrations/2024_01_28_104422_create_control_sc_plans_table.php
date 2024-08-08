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
        Schema::create('control_sc_plans', function (Blueprint $table) {
            $table->id();
            $table->integer('Priority');
            $table->date('Deadline');
            $table->time('time')->nullable();
            $table->string('source');
            $table->string('from');
            $table->string('status')->default("pending");
            $table->string('pricestate')->default("pending");
            $table->integer('cost');
            $table->text('description');
            $table->date('finishdata');
            $table->float("rate")->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_sc_plans');
    }
};
