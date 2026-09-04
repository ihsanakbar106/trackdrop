<?php

namespace App\Console\Commands;

use App\Http\Controllers\FulfillmentController;
use Illuminate\Console\Command;

class RegisterCargoWebhook extends Command
{
    protected $signature = 'cargo:register-webhook {--url= : Override webhook URL (defaults to APP_URL/api/webhooks/cargo-status-update)}';

    protected $description = 'Register Cargo status-update webhook (idempotent per type + customer_code)';

    public function handle()
    {
        $controller = new FulfillmentController();
        $url = $this->option('url') ?: null;
        $result = $controller->registerCargoWebhook($url);

        if (empty($result['success'])) {
            $this->error($result['message'] ?? 'Failed to register Cargo webhook');
            return 1;
        }

        $this->info('Cargo webhook registered / returned existing.');
        $this->line('URL: ' . ($result['webhook_url'] ?? ''));
        $this->line('HTTP: ' . ($result['status'] ?? ''));
        $this->line(json_encode($result['body'] ?? [], JSON_PRETTY_PRINT));

        return 0;
    }
}
