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
        Schema::create('main_journals', function (Blueprint $table) {
            $table->id();
            $table->string("invoice_group_id");
            $table->date("date");
            $table->unsignedBigInteger(column: "debit_id");
            $table->text(column: "debit_account_description");
            $table->unsignedBigInteger("credit_id");
            $table->text("credit_account_description");
            $table->integer("value");
            $table->text("description");
            $table->string("invoice_id");
            $table->foreign("debit_id")->references("id")->on("chart_accounts")->onUpdate("cascade")->onDelete("cascade");
            $table->foreign("credit_id")->references("id")->on("chart_accounts")->onUpdate("cascade")->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_journals');
    }
};
