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
        Schema::table('main_journals', function (Blueprint $table) {
            // Drop the invoice-related columns
            $table->dropColumn(['invoice_group_id', 'invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_journals', function (Blueprint $table) {
            // Re-add the dropped columns in case of rollback
            $table->string("invoice_group_id")->nullable(); // Default to nullable to prevent issues
            $table->string("invoice_id")->nullable();
        });
    }
};
