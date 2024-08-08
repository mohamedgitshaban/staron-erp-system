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
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid')->nullable();
            $table->date('EmploymentDate')->nullable()->default(now());
            $table->date('EmploymentDateEnd')->nullable();
            $table->enum('Reason', ['Long Leave', 'Termination', 'Resignation']);
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
        Schema::dropIfExists('user_logs');
    }
};
