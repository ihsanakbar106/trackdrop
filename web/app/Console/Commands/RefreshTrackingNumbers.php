<?php

namespace App\Console\Commands;

use App\Http\Controllers\FulfillmentController;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\Session;
use Illuminate\Console\Command;

class RefreshTrackingNumbers extends Command
{
    protected $signature = 'tracking:refresh
        {numbers* : Tracking numbers to refresh}
        {--force : Ignore enable_tracking / plan gates (still needs shop + plan_id)}';

    protected $description = 'Force carrier get-status + DB update for specific tracking numbers (Cargo/Track123)';

    public function handle(): int
    {
        $numbers = $this->argument('numbers');
        $controller = new FulfillmentController();
        $ok = 0;
        $fail = 0;

        foreach ($numbers as $number) {
            $fulfillment = Fulfillment::where('tracking_number', (string) $number)
                ->orderByDesc('updated_at')
                ->first();

            if (!$fulfillment) {
                $this->error("{$number}: fulfillment not found");
                $fail++;
                continue;
            }

            $shop = Session::find($fulfillment->session_id);
            if (!$shop || !$shop->plan_id) {
                $this->error("{$number}: shop/plan missing (session_id={$fulfillment->session_id})");
                $fail++;
                continue;
            }

            if (!$this->option('force') && !(int) $fulfillment->enable_tracking) {
                $status = strtolower((string) ($fulfillment->shipment_status ?? 'pending'));
                if (!in_array($status, ['', 'pending', 'notfound'], true)) {
                    $this->warn("{$number}: enable_tracking=0 and not pending — skip (use --force)");
                    $fail++;
                    continue;
                }
            }

            $country = null;
            $order = Order::where('session_id', $fulfillment->session_id)
                ->where('shopify_order_id', $fulfillment->shopify_order_id)
                ->first();
            if ($order) {
                if ($order->shipping_address && json_decode($order->shipping_address)) {
                    $country = json_decode($order->shipping_address)->country ?? null;
                } elseif ($order->billing_address && json_decode($order->billing_address)) {
                    $country = json_decode($order->billing_address)->country ?? null;
                }
            }

            $carrier = $fulfillment->tracking_company;
            $response = $controller->shipping_status(
                $fulfillment->fulfillment_id,
                $fulfillment->tracking_number,
                $carrier,
                $shop
            );

            if ($response === false) {
                $this->error("{$number}: shipping_status returned false (carrier={$carrier})");
                $fail++;
                continue;
            }

            $controller->shippingStatusUpdate($response, $fulfillment, $shop, $country);
            $fresh = $fulfillment->fresh();
            $this->info("{$number}: OK status={$fresh->shipment_status} event={$fresh->shipment_last_event}");
            $ok++;
        }

        $this->line("done ok={$ok} fail={$fail}");

        return $fail > 0 ? 1 : 0;
    }
}
