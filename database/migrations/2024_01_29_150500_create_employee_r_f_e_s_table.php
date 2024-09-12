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
        Schema::create('employee_r_f_e_s', function (Blueprint $table) {
            $table->id();
            $table->enum("request_type",['Sick Leave', 'Annual Vacation','Absent', 'Errands', 'Clock In Excuse', 'Clock Out Excuse']);
            $table->enum("hr_approve",["pending","rejected","approved"])->default("pending");
            $table->date("hr_approve_data")->default(now());
            $table->date("from_date");
            $table->date("to_date");
            $table->time("from_ci");
            $table->time("to_co");
            $table->text("description");
            $table->unsignedBigInteger("user_id");
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_r_f_e_s');
    }
};
