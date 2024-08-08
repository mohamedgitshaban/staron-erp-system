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
        Schema::create('warning_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid')->nullable();
            $table->integer('level');
            $table->date('date')->nullable()->default(now());
            $table->text('text')->nullable();
            $table->foreign('userid')
            ->references('id')
            ->on('users')
            ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warning_logs');
    }
};
