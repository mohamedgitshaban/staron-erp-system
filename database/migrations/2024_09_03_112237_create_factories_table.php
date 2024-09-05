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
        Schema::create('factories', function (Blueprint $table) {
            $table->id();
            $table->string("factory_name");
            $table->enum('factory_type', ['own', 'rent']);
            $table->enum('factory_status', ['active', 'not active']);
            $table->string("factory_location");
            $table->integer("amount");
            $table->string("factory_contract_file");
            $table->date("start_date");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factories');
    }
};
