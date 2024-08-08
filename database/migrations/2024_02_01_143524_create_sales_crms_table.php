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
        Schema::create('sales_crms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clients_id');
            $table->string("location");
            $table->string("tasbuilt")->nullable();
            $table->text("description");
            $table->string("status");
            $table->string("reason")->nullable();
            $table->integer('grade')->nullable();
            $table->unsignedBigInteger("asignby");
            $table->foreign('clients_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('asignby')
            ->references('id')
            ->on('users')
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
        Schema::dropIfExists('sales_crms');
    }
};
