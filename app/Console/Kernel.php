<?php

namespace App\Console;

use App\Jobs\AccurateJobHandler;
// use App\Jobs\AOL\AolSupplier;
use App\Jobs\AccurateRenewWebhook;
use App\Jobs\PreventiveMaintenance;
use App\Jobs\UpdateHMUnitProcessPM;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->job(new PreventiveMaintenance)->dailyAt('04:00');
        $schedule->job(new UpdateHMUnitProcessPM)->dailyAt('01:00');
        // $schedule->job(new AolSupplier)->everyFiveMinutes();
        // $schedule->job(new PreventiveMaintenance)->everyTwentySeconds();
        $schedule->job(new AccurateJobHandler())->everyTwentySeconds();
        $schedule->job(new AccurateRenewWebhook())->dailyAt('03:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
