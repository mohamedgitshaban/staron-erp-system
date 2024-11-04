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
            Schema::create('qc_applecations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('technecal_requests_id');
                $table->string("name");
                // $table->unsignedBigInteger("stockid");
                $table->integer("totalcost")->default(0);
                $table->float("grossmargen")->default(0);
                $table->float("salingprice")->default(0);
                // $table->foreign('stockid')->references('id')->on('stocks')->onDelete('cascade');
                $table->foreign('technecal_requests_id')->references('id')->on('technecal_requests')->onDelete('cascade');
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('qc_applecations');
        }
    };
