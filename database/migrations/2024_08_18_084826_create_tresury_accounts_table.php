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
        Schema::create('tresury_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("debit_id");
            $table->text("debit_account_description");
            $table->unsignedBigInteger("credit_id");
            $table->text("credit_account_description");
            $table->text("description");
            $table->integer("value");
            $table->date('collection_date');
            $table->string('collection_type');
            $table->string('type');
            $table->string("status")->default("pending");
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
        Schema::dropIfExists('tresury_accounts');
    }
};
