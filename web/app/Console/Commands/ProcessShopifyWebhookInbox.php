<?php

namespace App\Console\Commands;

use App\Support\ShopifyWebhookInbox;
use Illuminate\Console\Command;

class ProcessShopifyWebhookInbox extends Command
{
    protected $signature = 'shopify:process-webhook-inbox {--limit=100}';

    protected $description = 'Enqueue any Shopify webhook inbox files left after early ACK';

    public function handle(): int
    {
        $n = ShopifyWebhookInbox::processPending((int) $this->option('limit'));
        $this->info("Processed {$n} inbox file(s).");

        return 0;
    }
}
