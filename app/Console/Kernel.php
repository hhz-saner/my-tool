<?php

namespace App\Console;

use App\Console\Commands\AboutClassNotify;
use App\Console\Commands\Aliexpress;
use App\Console\Commands\GetProxy;
use App\Console\Commands\GetProxyTest;
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
        AboutClassNotify::class,
        GetProxy::class,
        Aliexpress::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(AboutClassNotify::class)->everyMinute()->withoutOverlapping();
        $schedule->command(GetProxy::class)->everyThirtyMinutes()->withoutOverlapping();
        $schedule->command(Aliexpress::class)->everyMinute()->withoutOverlapping();
        // $schedule->command('inspire')
        //          ->hourly();
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
