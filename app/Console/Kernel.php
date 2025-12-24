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
        Commands\clearAll::class,
        Commands\NotifyGroupsNearEndCommand::class,
        Commands\SearchReplaceCommand::class,
        Commands\GenerateSitemap::class,
        Commands\SyncSingleCourseToWordpress::class,
        Commands\SyncAllCoursesToWordpress::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('groups:notify-near-end')->cron('0 */6 * * *');
        
        // Generate sitemap daily at 2 AM
        $schedule->command('sitemap:generate all')->dailyAt('02:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
