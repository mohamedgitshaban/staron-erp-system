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
        Schema::create('main_journal_validations', function (Blueprint $table) {
            $table->id();
            $table->string("invoice_group_id");
            $table->date("date");
            $table->unsignedBigInteger("debit_id");
            $table->text("debit_account_description");
            $table->unsignedBigInteger("credit_id");
            $table->text("credit_account_description");
            $table->integer("value");
            $table->text("description")->nullable();
            $table->string("invoice_id");
            $table->unsignedBigInteger("requested_by");
            $table->enum("status", ["pending", "approved", "rejected"])->default("pending");
            $table->text("rejection_reason")->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign("debit_id")->references("id")->on("chart_accounts")->onUpdate("cascade")->onDelete("cascade");
            $table->foreign("credit_id")->references("id")->on("chart_accounts")->onUpdate("cascade")->onDelete("cascade");
            $table->foreign("requested_by")->references("id")->on("users")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_journal_validations');
    }
};
