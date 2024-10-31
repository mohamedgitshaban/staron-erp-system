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
        Schema::create('qc_applecation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('qc_applecations_id');
            $table->string("stockid");
            // $table->unsignedBigInteger("stockid");
            $table->text("description"); // removed the -> default("");
            $table->integer("price")->default(0);
            $table->float("quantity")->default();
            // $table->foreign('stockid')->references('id')->on('stocks')->onDelete('cascade');
            $table->foreign('qc_applecations_id')->references('id')->on('qc_applecations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qc_applecation_items');
    }
};
