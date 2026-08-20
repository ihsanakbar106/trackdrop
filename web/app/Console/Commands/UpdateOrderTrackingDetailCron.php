<?php

namespace App\Console\Commands;


use App\Http\Controllers\SyncController;
use App\Models\ErrorMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class UpdateOrderTrackingDetailCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_order_tracking_detail:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        try {
            $sync_controller = new SyncController();
            $sync_controller->sync_order_fulfillments_tracking();
//            $msg = new ErrorMessage();
//            $msg->message = 'sync order fulfillments trackings sync successully ';
//            $msg->save();
        }catch (\Exception $exception){
            $msg = new ErrorMessage();
            $msg->message = 'error in update order tracking detail cron job: '.json_encode($exception->getMessage()." line:".$exception->getLine());
            $msg->save();
        }
        return 0;
    }
}
