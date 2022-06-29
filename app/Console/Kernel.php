<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\GetStoresEks::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (App::environment('PRODUCTION')) {
            $schedule->command('wamas:sync')->everyMinute();
            $schedule->command('oms:send_order_groups')->sundays()->at('9:00');
            $schedule->command('oms:send_order_groups')->thursdays()->at('9:00');
            //$schedule->command('oms:send_report_waves')->dailyAt('7:00');
            //$schedule->command('oms:debug_tables')->weekly();
            //$schedule->command('oms:send_report_ppk_corrections')->saturdays()->at('23:59');
            $schedule->command('oms:backup_db')->dailyAt('22:00');
            $schedule->command('oms:syncstdps')->dailyAt('00:01');
            //$schedule->command('oms:update_classification_styles')->saturdays()->at('23:59');
        }
        if (App::enviroment('STAGING')) {
            $schedule->command('oms:check_backup')->dailyAt('23:00');
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
