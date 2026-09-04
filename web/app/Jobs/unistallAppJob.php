<?php

namespace App\Jobs;


use App\Models\AddressValidatorComponent;
use App\Models\AddressValidatorComponentCustomRule;
use App\Models\AddressValidatorComponentCustomSubRule;
use App\Models\AddressValidatorComponentCustomSubRuleProduct;
use App\Models\AddressValidatorComponentValidationRule;
use App\Models\AgeVerif;
use App\Models\AgeVerifCustomRule;
use App\Models\AgeVerifCustomSubRule;
use App\Models\AgeVerifCustomSubRuleProduct;
use App\Models\ApiStatistics;
use App\Models\Collection;
use App\Models\CustomCheckout;
use App\Models\CustomCheckoutCustomRule;
use App\Models\CustomCheckoutCustomSubRule;
use App\Models\CustomCheckoutCustomSubRuleProduct;
use App\Models\CustomCheckoutFormField;
use App\Models\DeliveryDate;
use App\Models\DeliveryDateCustomRule;
use App\Models\DeliveryDateCustomSubRule;
use App\Models\DeliveryDateCustomSubRuleProduct;
use App\Models\ErrorMessage;
use App\Models\Fulfillment;
use App\Models\GiftWrap;
use App\Models\GiftWrapComponent;
use App\Models\GiftWrapCustomRule;
use App\Models\GiftWrapCustomSubRule;
use App\Models\GiftWrapCustomSubRuleProduct;
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
    public $timeout = 100000000000;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $session_id;
    public function __construct($session_id)
    {
        $this->session_id = $session_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $session_id = $this->session_id;

        try {


//        delete charges
            $charges = \App\Models\Charge::where('session_id',$session_id)->delete();


//        delete collections
            $collections = Collection::where('session_id',$session_id)->get();
            if (count($collections)) {
                foreach ($collections as $collection) {
                    $collection_product = \App\Models\CollectionProduct::where('collection_id', $collection->id)->first();
                    if (isset($collection_product)) {
                        $collection_product->forceDelete();
                    }
                    $collection->forceDelete();
                }
            }

//        delete products
            Product::where('session_id',$session_id)->delete();
            Variant::where('session_id',$session_id)->delete();
            ApiStatistics::where('session_id',$session_id)->delete();
            Fulfillment::where('session_id',$session_id)->delete();
            Order::where('session_id',$session_id)->delete();
            LineItem::where('session_id',$session_id)->delete();
            TrackingPage::where('session_id',$session_id)->delete();


        }catch (\Exception $e) {
            $error = new ErrorMessage();
            $error->message = "Error Message In APP Unistall, Line#".$e->getLine()." Error: ".json_encode($e->getMessage());
            $error->save();
        }

    }
}
