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
        Schema::create('cutting_data_pieces', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("cutting_data_id");
            $table->string("type");
            $table->float("low_hight");
            $table->float("high_hight");
            $table->float("low_width");
            $table->float("high_width");
            $table->float("low_length");
            $table->float("high_length");
            $table->foreign('cutting_data_id')
            ->references('id')
            ->on('cutting_data')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cutting_data_pieces');
    }
};
