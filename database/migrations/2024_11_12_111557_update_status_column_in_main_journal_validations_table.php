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
        Schema::table('main_journal_validations', function (Blueprint $table) {
            // Update the enum values for the 'status' column
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'pending account creation'
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_journal_validations', function (Blueprint $table) {
            // Revert back to the original enum values
            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending')->change();
        });
    }
};
