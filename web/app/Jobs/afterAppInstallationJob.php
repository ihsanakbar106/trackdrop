<?php

namespace App\Jobs;

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ProductController;
use App\Models\Session;
use App\Models\Translation;
use App\Services\ShopifyTokenService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Shopify\Clients\Rest;

class afterAppInstallationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 100000000000;
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
        if($session) {
            $records = [
                [
                    'shop_id' => $session->id,
                    'language' => 'en',
                    'is_default' => 1,
                    'track_your_order' => 'TRACK YOUR ORDER',
                    'order_number' => 'Order number',
                    'tracking_number' => 'Tracking number',
                    'email_phone_number' => 'Email or Phone number',
                    'track_btn' => 'Track',
                    'order_number_placeholder' => 'Enter your order number',
                    'order_number_error' => 'Please enter order number',
                    'tracking_number_placeholder' => 'Enter your tracking number',
                    'tracking_number_error' => 'Please enter tracking number',
                    'email_phone_number_placeholder' => 'Enter your email or phone number',
                    'email_phone_number_error' => 'Please enter your email or phone number',
                    'order_status_text' => 'Your order is',
                    'carrier_title' => 'Carrier',
                    'product_title' => 'Product (s)',
                    'page_not_publish' => 'Tracking page not published!',
                    'package_content' => 'Package Contents',
                    'pb_ordered' => 'Ordered',
                    'pb_in_transit' => 'In Transit',
                    'pb_out_for_delivery' => 'Out for Delivery',
                    'pb_delivered' => 'Delivered',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'shop_id' => $session->id,
                    'language' => 'he',
                    'is_default' => 0,
                    'track_your_order' => 'מעקב משלוחים',
                    'order_number' => 'מספר הזמנה',
                    'tracking_number' => 'מספר מעקב',
                    'email_phone_number' => 'מייל',
                    'track_btn' => 'מעקב',
                    'order_number_placeholder' => 'הוסף מספר הזמנה',
                    'order_number_error' => 'בבקשה הוסף מספר הזמנה',
                    'tracking_number_placeholder' => 'הוסף מספר מעקב',
                    'tracking_number_error' => 'בבקשה הוסף מספר מעקב תקין',
                    'email_phone_number_placeholder' => 'הוסף מייל',
                    'email_phone_number_error' => 'בבקשה הוסף מייל',
                    'order_status_text' => 'ההזמנה שלך:',
                    'carrier_title' => 'מוֹבִיל',
                    'product_title' => 'מוצר(ים)',
                    'page_not_publish' => 'דף המעקב לא פורסם!',
                    'package_content' => 'תכולת החבילה',
                    'pb_ordered' => 'שהוזמן',
                    'pb_in_transit' => 'בְּמַעֲבָר',
                    'pb_out_for_delivery' => 'יוצא למשלוח',
                    'pb_delivered' => 'נמסר',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($records as $record) {
                Translation::updateOrCreate(
                // Match condition
                    ['shop_id' => $record['shop_id'], 'language' => $record['language']],
                    // Update or create values
                    $record
                );
            }
            $translation = Translation::where('shop_id', $session->id)->where('is_default', 1)->first();
            if ($translation == null) {
                $translation = new Translation();
                $translation->shop_id = $session->id;
                $translation->is_default = 1;
                $translation->save();
            }
            $client = new Rest($session->shop, (new ShopifyTokenService())->getValidAccessToken($session->shop));

            $shop_metafield = $client->post('/admin/metafields.json', [
                "metafield" => array(
                    "key" => 'translation',
                    "value" => json_encode($translation),
                    "type" => "json_string",
                    "namespace" => "autotrack"
                )
            ]);
            $product_controller = new ProductController();
            if (isset($session)) {
                $product_controller->sync_products($session->shop, null);
            }
        }

    }
}
