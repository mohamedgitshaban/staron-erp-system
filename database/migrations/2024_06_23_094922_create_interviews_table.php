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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->date("interview_date");
            $table->time("interview_time");
            $table->time("cv_file");
            $table->string("attend")->default("pending");
            $table->date("start_notes_period")->default(now());
            $table->date("end_notes_period")->default(now());
            $table->integer("expected_salary")->default(0);
            $table->float("grade")->default(null);
            $table->string("manager_approve")->default("pending");
            $table->string("manager_reason")->default("");
            $table->string("admin_approve")->default("pending");
            $table->string("admin_reason")->default("");
            $table->string("job_offer")->default("");
            $table->text("manager_notes")->default("");
            $table->unsignedBigInteger("reqrurmentsid");
            $table->foreign('reqrurmentsid')
            ->references('id')
            ->on('reqrurments')
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
        Schema::dropIfExists('interviews');
    }
};
