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
        Schema::create('reqrurments', function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->text("description");
            $table->string("hrstatus")->default("pending");
            $table->string("adminstatus")->default("ask");
            $table->date("hr_approve_data")->default(now());
            $table->date("admin_approve_data")->default(now());
            $table->string("status")->default("pending");
            $table->string("cvs")->default("");
            $table->string("interviewtime")->default("");
            $table->unsignedBigInteger("asignby");
            $table->timestamps();
            $table->foreign('asignby')
            ->references('id')
            ->on('users')
            ->onUpdate('cascade')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reqrurments');
    }
};
