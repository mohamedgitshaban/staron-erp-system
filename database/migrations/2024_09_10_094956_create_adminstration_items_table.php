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
        Schema::create('adminstration_items', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->enum("type",["supplies","utilites","miscelleneouses","subscliptions","maintainances"]);
            $table->integer("amount");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adminstration_items');
    }
};