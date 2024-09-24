<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            $table->enum('department', [
                "administration",
                "Executive",
                "Human Resources",
                "Technical Office",
                "Sales Office",
                "Operation Office",
                "Control Office",
                "Supply Chain",
                "Markiting",
                "Research & Development",
                "Finance"
            ]);
            $table->string('profileimage');
            $table->string('job_role');
            $table->enum('job_tybe',["Full Time","Part Time"]);
            $table->string('pdf');
            $table->unsignedBigInteger('Supervisor')->nullable();
            $table->integer('MedicalInsurance')->nullable()->default(0);
            $table->integer('VacationBalance')->nullable()->default(0);
            $table->integer('SocialInsurance')->nullable()->default(0);
            $table->integer('Trancportation')->nullable()->default(0);
            $table->string('phone')->nullable();
            $table->boolean('overtime');
            $table->float('overtime_value');

            $table->date('EmploymentDate')->nullable()->default(now());
            $table->boolean('isemploee')->default(true);
            $table->integer('kpi')->nullable();
            $table->integer('tax')->nullable();
            $table->enum('TimeStamp',["Office","Whats App"])->default("Office");
            $table->enum('grade',["Manager","First Staff","Seconed Staff","Third Staff","Forth Staff"])->default("Manager");
            $table->enum('segment',["G & A","COR"])->default("G & A");
            $table->enum('startwork', [
                'Sunday',
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday'
            ])->default("sunday");
            $table->enum('endwork', [
                'Sunday',
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday'
            ])->default("tursday");
            $table->time('clockin');
            $table->time('clockout');
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
