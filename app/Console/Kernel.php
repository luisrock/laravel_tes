<?php

namespace App\Console;

use App\Jobs\SendRenewalReminders;
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
        //
    ];

    /**
     * Agendamento centralizado (antes disperso no crontab do servidor).
     *
     * Em produção: uma única entrada no cron/Vito —
     * * * * * php8.3 /home/vito/tesesesumulas.com.br/artisan schedule:run >> /dev/null 2>&1
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('queue:work', ['--stop-when-empty'])
            ->dailyAt('00:00')
            ->withoutOverlapping();

        $schedule->command('sitemap:generate')
            ->dailyAt('06:00')
            ->withoutOverlapping();

        $schedule->command('matomo:sync')
            ->weeklyOn(1, '03:00')
            ->withoutOverlapping();

        $schedule->command('newsletters:import')
            ->weeklyOn(2, '23:00')
            ->withoutOverlapping();

        $schedule->job(new SendRenewalReminders)
            ->dailyAt('10:00')
            ->withoutOverlapping();

        $schedule->command('newsletter:sync')
            ->everySixHours()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
