<?php

namespace App\Jobs;

use App\Http\Controllers\SyncController;
use App\Models\Session;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AllOrderCreateUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $shop;
    public $order;
    public function __construct($order,Session $shop)
    {
        $this->shop = $shop;
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = $this->order;
        $shop = $this->shop;

        $sync_controller = new SyncController();
        $sync_controller->createUpdateOrder($order, $shop);
    }
}
