<?php

namespace App\Console;

use App\Console\Commands\BackupFilesCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        BackupFilesCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command(BackupFilesCommand::class)
            ->everyMinute()
            ->withoutOverlapping();
    }
}
