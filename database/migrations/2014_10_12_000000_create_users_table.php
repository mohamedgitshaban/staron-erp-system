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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('hr_code')->unique();
            $table->date('date');
            $table->string('address');
            $table->string('password');
            $table->Integer('salary');
            $table->string('department');
            $table->string('profileimage');
            $table->string('job_role');
            $table->string('job_tybe');
            $table->string('pdf');
            $table->unsignedBigInteger('Supervisor')->nullable();
            $table->integer('MedicalInsurance')->nullable()->default(0);
            $table->integer('VacationBalance')->nullable()->default(0);
            $table->integer('SocialInsurance')->nullable()->default(0);
            $table->integer('Trancportation')->nullable()->default(0);
            $table->string('phone')->nullable();
            $table->date('EmploymentDate')->nullable()->default(now());
            $table->boolean('isemploee')->default(true);
            $table->integer('kpi')->nullable();
            $table->integer('tax')->nullable();
            $table->string('TimeStamp')->default("office");
            $table->string('grade')->default("maneger");
            $table->string('segment')->default("G&A");
            $table->string('startwork')->default("sunday");
            $table->string('endwork')->default("tursday");
            $table->time('clockin')->default("9:00");
            $table->date('clockout')->default(now());
            $table->time('last_login')->default("6:00");
            $table->foreign('Supervisor')
            ->references('id')
            ->on('users')
            ->onDelete('set null'); // or you can use 'cascade' or other options based on your requirements

            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@erp.com',
                'hr_code' => 'HR0',
                'phone' => '0111551818',
                'date' => '1992-03-15',
                'address' => '456 Oak Street, Townsville',
                'password' => Hash::make('password123!'),
                'salary' => 60000,
                'department' => 'admin',
                'Supervisor' => null,
                'EmploymentDate' => now(),
                'VacationBalance' => 20,
                'profileimage' => '/uploads/profileimages/images.png',
                'job_role' => 'developer',
                'job_tybe' => 'Part-time',
                'pdf' => '/uploads/userdoc/doc.docx',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]

            // Add more data as needed
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
