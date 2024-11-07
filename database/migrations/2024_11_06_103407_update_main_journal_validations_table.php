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
            // Remove columns related to invoice and descriptions
            $table->dropColumn(['invoice_group_id', 'invoice_id', 'debit_account_description', 'credit_account_description']);
            
            // Add new nullable columns for ticket trail and originating module
            $table->text('ticket_trail')->nullable()->after('status');
            $table->string('originating_module')->nullable()->after('ticket_trail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_journal_validations', function (Blueprint $table) {
            // Re-add the removed columns with their original specifications
            $table->string("invoice_group_id")->after('id');
            $table->string("invoice_id")->after('description');
            $table->text("debit_account_description")->after('debit_id');
            $table->text("credit_account_description")->after('credit_id');

            // Drop the newly added columns
            $table->dropColumn(['ticket_trail', 'originating_module']);
        });
    }
};
