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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_crms_id');
            $table->string("contractstatus")->default("pending");
            $table->date("contractstartdate")->nullable();
            $table->date("contractenddate")->nullable();
            $table->string("contractdata")->default("");
            $table->integer("contractValue")->default(0);
            $table->foreign('sales_crms_id')->references('id')->on('sales_crms')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
