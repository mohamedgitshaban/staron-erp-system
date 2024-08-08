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
        Schema::create('package_applecations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('technecal_packages_id');
            $table->unsignedBigInteger("stockid");
            $table->text("description")->default("");
            $table->integer("price")->default(0);
            $table->float("quantity")->default();
            $table->foreign('stockid')->references('id')->on('stocks')->onDelete('cascade');
            $table->foreign('technecal_packages_id')->references('id')->on('technecal_packages')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_applecations');
    }
};
