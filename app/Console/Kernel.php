<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected function schedule(Schedule $schedule)
    {
        // Log::info("Command is working fine!");

        // $date = date('Y-m-d');
        // $schedule->command('dev:report ' . $date . ' ' . $date)->cron("*/1 * * * *")->runInBackground();
        // $schedule->command('cam-attendance:log')->cron("*/1 * * * *")->runInBackground();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
