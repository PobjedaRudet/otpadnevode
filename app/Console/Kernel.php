<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\SendMonthlyCompanyDeltaReport::class,
        \App\Console\Commands\MakeWordTemplate::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run on the 1st day of every month at 07:00 (uses app timezone)
        $schedule->command('reports:send-monthly')->monthlyOn(1, '7:00');
        // Run daily at 07:05 on weekdays only; command will also self-guard against weekends
        $schedule->command('reports:send-daily')
            ->weekdays()
            ->at('7:05');
        // Run Mondays at 07:06 for weekend window (Fri 23:30 -> Mon 07:00)
        $schedule->command('reports:send-weekly-weekend')->weeklyOn(1, '7:06');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
