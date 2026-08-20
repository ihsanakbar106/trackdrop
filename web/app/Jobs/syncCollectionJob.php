<?php

namespace App\Jobs;

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ProductController;
use App\Models\Session;
use App\Product;
use App\User;
use App\Variant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class syncCollectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 3600; // 2 minute
    public $tries = 5;
    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $session_name;
    public function __construct($session_name)
    {
        $this->session_name = $session_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $session_name = $this->session_name;
        $session = Session::where('shop',$session_name)->first();
        $collection_controller = new CollectionController();
        if(isset($session)){
            $collection_controller->sync_collections($session->shop);
        }

    }
}
