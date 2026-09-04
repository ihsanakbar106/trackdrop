<?php

namespace App\Console;

use App\Models\Fulfillment;
use App\Models\Session;
use Carbon\Carbon;
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
        Commands\PlanChargeStatus::class,
        Commands\UpdateOrderTrackingDetailCron::class,
        Commands\RegisterCargoWebhook::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('PlanChargeStatus:cron')->everyFourHours();
        $schedule->command('update_order_tracking_detail:cron')->everyTwoHours()->withoutOverlapping();

        $schedule->call(function () {
            $date=Carbon::now()->subDays(6)->toDateString();
            $users = Session::whereNotNull('plan_id') // Check where plan_id is not null
            ->whereDate('created_at', '<=', $date) // Check where created_at <= $date
            ->get();
            foreach ($users as $user) {
                try {
                    Fulfillment::where('session_id',$user->id)->update(['enable_tracking'=>1]);
                }catch (\Exception $e){
                    continue;
                }
            }
//            $helper->cloudwaysSSLInstall();
        })->name('update_enable_shipment:cron')->daily();
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
