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
        Schema::create('package_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_crms_id');
            $table->string("Packedgestatus")->default("pending");
            $table->date("Packedgestartdate")->nullable();
            $table->date("Packedgeenddate")->nullable();
            $table->string("Packedgedata")->default("");
            $table->foreign('sales_crms_id')->references('id')->on('sales_crms')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_data');
    }
};
