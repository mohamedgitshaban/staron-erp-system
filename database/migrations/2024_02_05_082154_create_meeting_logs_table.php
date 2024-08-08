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
        Schema::create('meeting_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clients_id');
            $table->text("reason");
            $table->text("result")->default("");
            $table->string("status");
            $table->string("type");
            $table->date("date");
            $table->date("nextactivity")->default(null);
            $table->time("time");
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
        Schema::dropIfExists('meeting_logs');
    }
};
