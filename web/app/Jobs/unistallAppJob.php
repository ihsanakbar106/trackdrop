<?php

namespace App\Jobs;

use App\Models\ApiStatistics;
use App\Models\Collection;
use App\Models\ErrorMessage;
use App\Models\Fulfillment;
use App\Models\LineItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\TrackingPage;
use App\Models\Variant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class unistallAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Keep under typical queue:work --timeout so the job finishes instead of retry-looping. */
    public $timeout = 3600;

    public $tries = 3;

    public $backoff = [30, 60, 120];

    public $session_id;

    public function __construct($session_id)
    {
        $this->session_id = $session_id;
    }

    public function handle()
    {
        $session_id = $this->session_id;

        try {
            \App\Models\Charge::where('session_id', $session_id)->delete();

            $collectionIds = Collection::where('session_id', $session_id)->pluck('id');
            if ($collectionIds->isNotEmpty()) {
                \App\Models\CollectionProduct::whereIn('collection_id', $collectionIds)->delete();
                Collection::where('session_id', $session_id)->delete();
            }

            // Chunk large tables so uninstall does not load everything into memory / time out.
            Product::where('session_id', $session_id)->orderBy('id')->chunkById(500, function ($rows) {
                Product::whereIn('id', $rows->pluck('id'))->delete();
            });
            Variant::where('session_id', $session_id)->orderBy('id')->chunkById(500, function ($rows) {
                Variant::whereIn('id', $rows->pluck('id'))->delete();
            });
            ApiStatistics::where('session_id', $session_id)->orderBy('id')->chunkById(500, function ($rows) {
                ApiStatistics::whereIn('id', $rows->pluck('id'))->delete();
            });
            Fulfillment::where('session_id', $session_id)->orderBy('id')->chunkById(500, function ($rows) {
                Fulfillment::whereIn('id', $rows->pluck('id'))->delete();
            });
            LineItem::where('session_id', $session_id)->orderBy('id')->chunkById(500, function ($rows) {
                LineItem::whereIn('id', $rows->pluck('id'))->delete();
            });
            Order::where('session_id', $session_id)->orderBy('id')->chunkById(500, function ($rows) {
                Order::whereIn('id', $rows->pluck('id'))->delete();
            });
            TrackingPage::where('session_id', $session_id)->delete();
        } catch (\Exception $e) {
            $error = new ErrorMessage();
            $error->message = "Error Message In APP Unistall, Line#" . $e->getLine() . " Error: " . json_encode($e->getMessage());
            $error->save();
            // Do not rethrow: uninstall webhook should complete; error is logged for follow-up.
        }
    }
}
