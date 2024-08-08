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
        Schema::create('technecal_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_crms_id');
            $table->string("qcstatus")->default("pending");
            $table->date("qcstartdate")->default(now());
            $table->date("qcenddate")->nullable();
            $table->integer("totalprice")->nullable();
            $table->string("qcdata")->default("");
            $table->text("reason")->default("");
            $table->foreign('sales_crms_id')->references('id')->on('sales_crms')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technecal_requests');
    }
};
