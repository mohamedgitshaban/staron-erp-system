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
        Schema::create('incentive_payrolls', function (Blueprint $table) {

                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->date('Date');
                $table->Integer('workdays');
                $table->Integer('holidays');
                $table->Integer('attendance');
                $table->float('PresenceMargin');
                $table->float('kpi');
                $table->float('performanceRate');
                $table->float('performanceIncentive');
                $table->float('TotalPay');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incentive_payrolls');
    }
};
