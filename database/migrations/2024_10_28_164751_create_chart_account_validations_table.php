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
        Schema::create('chart_account_validations', function (Blueprint $table) {
            $table->id();
            // The name of the final account being requested (e.g., "Bright White")
            $table->string('name', 255);
            // Source or department requesting the new account (e.g., "Supply Chain")
            $table->string('request_source', 255);
            // ID of the user making the request
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            // Parent ID to establish the relationship within the chart of accounts
            $table->foreignId('parent_id')->nullable()->constrained('chart_accounts')->onDelete('set null');
            // JSON field to store the hierarchy structure as an array
            $table->json('hierarchy')->nullable();
            // Reason for rejection (only used if the request is rejected)
            $table->string('rejection_reason', 1000)->nullable();
            // Status of the request, defaulting to "pending"
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            // Timestamps to record when the request was created and last updated
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_account_validations');
    }
};
