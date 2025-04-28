<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\SyncStorage::class,
    ];

    protected function bootstrappers()
    {
        return array_merge(parent::bootstrappers(), [
            'App\\Console\\Commands\\SyncStorage',
        ]);
    }

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('storage:sync')->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
