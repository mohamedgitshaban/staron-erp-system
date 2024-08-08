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
        Schema::table('technecal_requests', function (Blueprint $table) {
            $table->unsignedBigInteger("asign_for_user");
            $table->foreign('asign_for_user')
            ->references('id')
            ->on('users')
            ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technecal_requests', function (Blueprint $table) {
            //
        });
    }
};
