<?php

namespace App\Jobs;

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\HelperController;
use App\Models\ErrorMessage;
use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class collectionCreateUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Must stay <= queue:work --timeout (Cloudways). */
    public $timeout = 120;

    public $tries = 3;

    /** @var int[] */
    public $backoff = [15, 45, 90];

    /** @var string|null */
    public $shopDomain;

    /**
     * Legacy property for jobs queued before shopDomain migration.
     * @var Session|null
     */
    public $session;

    public $shopify_id;

    /**
     * @param  string|Session  $shopDomainOrSession
     * @param  mixed  $shopify_id
     */
    public function __construct($shopDomainOrSession, $shopify_id)
    {
        $this->shopify_id = $shopify_id;
        $this->shopDomain = is_object($shopDomainOrSession) && isset($shopDomainOrSession->shop)
            ? (string) $shopDomainOrSession->shop
            : (string) $shopDomainOrSession;
        // Never serialize Session — uninstall/reinstall breaks SerializesModels restore.
        $this->session = null;
    }

    public function handle()
    {
        try {
            $session = $this->resolveSession();
            if (!$session || empty($session->shop)) {
                return;
            }

            $c_controller = new CollectionController();
            $helper = new HelperController();
            $api = $helper->getShopApi($session->shop);
            $query = <<<GRAPHQL
                query {
                    collection(id: "gid://shopify/Collection/$this->shopify_id"){
                    id
                    handle
                    title
                    description
                    image{
                        url
                    }
                }
            GRAPHQL;
            $response = $api->graph($query);
            if ($response['errors'] == false) {
                if ($response['body']['data']['collection']) {
                    $collection = $response['body']['data']['collection'];
                    $collectionData['node']['container'] = $collection;
                    $c_controller->CreateUpdateCollection($collectionData, $session);
                }
            }
        } catch (\Throwable $e) {
            try {
                $msg = new ErrorMessage();
                $msg->message = 'collectionCreateUpdateJob error: ' . $e->getMessage() . ' line:' . $e->getLine();
                $msg->save();
            } catch (\Throwable $ignored) {
            }
            throw $e;
        }
    }

    protected function resolveSession(): ?Session
    {
        if (is_string($this->shopDomain) && $this->shopDomain !== '') {
            return Session::where('shop', $this->shopDomain)->first();
        }
        if (is_object($this->session) && isset($this->session->shop)) {
            return $this->session instanceof Session
                ? $this->session
                : Session::where('shop', $this->session->shop)->first();
        }

        return null;
    }
}
