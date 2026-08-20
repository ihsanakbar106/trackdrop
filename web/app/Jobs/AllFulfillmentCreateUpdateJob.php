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

class AllFulfillmentCreateUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $shop;
    public $fulfillment_api;
    public function __construct($fulfillment_api,Session $shop)
    {
        $this->shop = $shop;
        $this->fulfillment_api = $fulfillment_api;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fulfillment_api = $this->fulfillment_api;
        $shop = $this->shop;

        $sync_controller = new SyncController();
        $sync_controller->createUpdateFufillment($fulfillment_api, $shop);
    }
}
