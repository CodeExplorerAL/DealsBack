<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // commands() 方法：負責載入自定義的 Artisan 命令。在這裡，我們載入了 UpdateExpiredArticlesStatus 命令
        $schedule->command('articles:update-expired-status')
        ->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // $commands 屬性：列出了應用程序提供的 Artisan 命令
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // ...
        \App\Console\Commands\UpdateExpiredArticlesStatus::class,
    ];
}
