<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Services\ShopifyTokenService;
use Illuminate\Console\Command;

class MigrateExpiringShopifyTokens extends Command
{
    protected $signature = 'shopify:migrate-expiring-tokens
                            {--shop= : Shop domain, e.g. store.myshopify.com (required)}
                            {--dry-run : Show the row without calling Shopify}';

    protected $description = 'Cycle one shop from a legacy offline token to an expiring token + refresh_token. Irreversible for that shop.';

    public function handle(): int
    {
        $shop = $this->normalizeShop((string) $this->option('shop'));
        if ($shop === '') {
            $this->error('Pass --shop=store.myshopify.com (one shop at a time).');

            return 1;
        }

        $session = Session::query()
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->where(function ($q) use ($shop) {
                $q->where('shop', $shop)->orWhere('session_id', $shop)->orWhere('session_id', 'offline_' . $shop);
            })
            ->first();
        if (!$session) {
            $this->error("No access_token row for {$shop}");

            return 1;
        }

        $this->line("shop={$session->shop} id={$session->id} token_kind=" . ($session->token_kind ?? 'legacy') . ' has_refresh=' . ($session->refresh_token ? 'yes' : 'no') . ' token_len=' . strlen((string) $session->access_token));
        $this->line('artisan client_id=' . env('SHOPIFY_API_KEY'));

        $already = $session->refresh_token && ($session->token_kind ?? '') === 'expiring';
        if ($this->option('dry-run')) {
            $this->info($already ? 'Already expiring. Nothing to do.' : 'Would call Shopify token exchange (expiring=1).');

            return 0;
        }

        if ($already) {
            $this->info('Already expiring. Nothing to do.');

            return 0;
        }

        $this->warn('Shopify will revoke the current non-expiring token if this succeeds.');

        try {
            (new ShopifyTokenService())->migrateToExpiring($session->shop);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->warn('HTTP/exchange failures leave the current access_token in place. Persist failures after a 200 need a reinstall.');

            return 1;
        }

        $this->info('OK. Stored expiring access_token + refresh_token.');

        return 0;
    }

    private function normalizeShop(string $shop): string
    {
        $shop = strtolower(trim($shop));
        if (substr($shop, -9) === '--dry-run') {
            $shop = rtrim(substr($shop, 0, -9));
            $this->input->setOption('dry-run', true);
        }
        if (strpos($shop, 'offline_') === 0) {
            $shop = substr($shop, strlen('offline_'));
        }
        if ($shop !== '' && strpos($shop, '.') === false) {
            $shop .= '.myshopify.com';
        }

        return $shop;
    }
}
