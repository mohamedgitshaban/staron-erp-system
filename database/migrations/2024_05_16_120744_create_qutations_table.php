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
        Schema::create('qutations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_crms_id');
            $table->string("Qutationstatus")->default("pending");
            $table->string("reason")->nullable();
            $table->date("Qutationstartdate")->nullable();
            $table->date("Qutationenddate")->nullable();
            $table->string("Qutationdata")->default("");
            $table->integer("TotalProjectSellingPrice")->default(0);
            $table->integer("ProjectGrossMargin")->default(0);
            $table->foreign('sales_crms_id')->references('id')->on('sales_crms')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qutations');
    }
};
