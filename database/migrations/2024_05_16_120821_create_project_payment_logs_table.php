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
        Schema::create('project_payment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_crms_id');
            $table->string("paymentstatus")->default("pending");
            $table->date("paymentdate")->nullable();
            $table->string("type")->nullable();
            $table->foreign('sales_crms_id')->references('id')->on('sales_crms')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_payment_logs');
    }
};
