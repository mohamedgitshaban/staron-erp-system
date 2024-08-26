<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\AttendanceLog;
use App\Console\Commands\MonthPayroll;
class Kernel extends ConsoleKernel
{

    protected $commands=[
        \App\Console\Commands\AttendanceLog::class,
        \App\Console\Commands\MonthPayroll::class
    ];
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command("attendance:log")->dailyAt("17:24");
        $schedule->command("month:payroll")->monthlyOn(26,"22:00");

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
