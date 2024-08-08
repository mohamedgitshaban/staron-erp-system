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
        Schema::create('finance_report_submitions', function (Blueprint $table) {
            $table->id();
            $table->date("date")->default(now());
            $table->string("report1")->nullable();
            $table->string("report2")->nullable();
            $table->string("report3")->nullable();
            $table->string("report4")->nullable();
            $table->string("report5")->nullable();
            $table->string("report6")->nullable();
            $table->string("report7")->nullable();
            $table->string("report8")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_report_submitions');
    }
};
