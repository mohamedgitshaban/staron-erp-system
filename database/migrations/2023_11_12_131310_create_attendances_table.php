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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('Date');
            $table->time('Clock_In')->nullable();
            $table->time('Clock_Out')->nullable();
            $table->boolean('Must_C_In')->nullable();
            $table->boolean('Must_C_Out')->nullable();
            $table->boolean('Absent')->nullable();
            $table->time('late')->nullable();
            $table->time('Work_Time')->nullable();
            $table->text('note')->nullable();
            $table->boolean('Exception')->nullable();
            $table->float('addetion');
            $table->float('deduction');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
