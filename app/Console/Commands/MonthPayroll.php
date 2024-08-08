<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MonthPayroll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'month:payroll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this command upload the month payroll for all employee who attendance is more than 1';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
