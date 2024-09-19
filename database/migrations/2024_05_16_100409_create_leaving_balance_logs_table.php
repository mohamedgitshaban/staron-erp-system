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
        Schema::create('leaving_balance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid');
            $table->date('date')->nullable();
            $table->text('text');
            $table->integer('amount');
            $table->enum('type',["incress","decress"]);
            $table->foreign('userid')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaving_balance_logs');
    }
};
