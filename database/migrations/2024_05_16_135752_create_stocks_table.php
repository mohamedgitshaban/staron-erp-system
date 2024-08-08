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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoriesid');
            $table->string("code")->unique();
            $table->string("color");
            $table->float("quantity");
            $table->integer("priceperunit");
            $table->integer("lastpriceforit");
            $table->string("unit");
            $table->foreign('categoriesid')->references('id')->on('categories')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
