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
        Schema::create('operation_asbuilts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_crms_id');
            $table->string("status")->default("pending");
            $table->date("start");
            $table->date("Actualstart")->nullable();
            $table->date("end");
            $table->date("Actualend")->nullable();
            $table->string("data")->nullable();
            $table->foreign('sales_crms_id')->references('id')->on('sales_crms')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_asbuilts');
    }
};
