<?php

namespace App\Jobs;

use App\Http\Controllers\SyncController;
use App\Models\ErrorMessage;
use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AllOrderCreateUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Must stay <= queue:work --timeout (Cloudways). */
    public $timeout = 120;

    public $tries = 3;

    /** @var int[] */
    public $backoff = [15, 45, 90];

    /**
     * Shop domain string — never serialize Session (uninstall → ModelNotFoundException).
     * @var string|null
     */
    public $shopDomain;

    /**
     * Legacy property for jobs already queued before shopDomain migration.
     * @var Session|string|null
     */
    public $shop;

    public $order;

    /**
     * @param  object  $order
     * @param  string|Session  $shopDomain
     */
    public function __construct($order, $shopDomain)
    {
        $this->order = $order;
        $this->shopDomain = is_object($shopDomain) && isset($shopDomain->shop)
            ? (string) $shopDomain->shop
            : (string) $shopDomain;
        // Do not assign Session to $this->shop — avoids SerializesModels restore failures.
        $this->shop = null;
    }

    public function handle()
    {
        try {
            $domain = $this->resolveShopDomain();
            if ($domain === '' || !$this->order) {
                return;
            }

            $shop = Session::where('shop', $domain)->first();
            if (!$shop) {
                return;
            }

            (new SyncController())->createUpdateOrder($this->order, $shop);
        } catch (\Throwable $e) {
            try {
                $msg = new ErrorMessage();
                $msg->message = 'AllOrderCreateUpdateJob error: ' . $e->getMessage() . ' line:' . $e->getLine();
                $msg->save();
            } catch (\Throwable $ignored) {
            }
            // Re-throw so $tries / backoff work (do not silently mark job successful).
            throw $e;
        }
    }

    protected function resolveShopDomain(): string
    {
        if (is_string($this->shopDomain) && $this->shopDomain !== '') {
            return $this->shopDomain;
        }
        if (is_object($this->shop) && isset($this->shop->shop)) {
            return (string) $this->shop->shop;
        }
        if (is_string($this->shop) && $this->shop !== '') {
            return $this->shop;
        }

        return '';
    }
}
