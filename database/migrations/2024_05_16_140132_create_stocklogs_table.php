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
        Schema::create('stocklogs', function (Blueprint $table) {
            $table->id();
            $table->string("type");//
            $table->string("status");
            $table->unsignedBigInteger('stocksid');//
            $table->text("Note")->nullable();//
            $table->float("quantity");//
            $table->string("source");//
            $table->float("cost");
            //for project
            $table->unsignedBigInteger('sales_crmsid')->nullable();
            //for procurment
            $table->unsignedBigInteger('supplyersid')->nullable();
            $table->string("file")->nullable();



            $table->foreign('supplyersid')->references('id')->on('supplyers')->onDelete('cascade');
            $table->foreign('sales_crmsid')->references('id')->on('sales_crms')->onDelete('cascade');
            $table->foreign('stocksid')->references('id')->on('stocks')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocklogs');
    }
};
