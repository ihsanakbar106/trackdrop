<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Services\ShopifyTokenService;
use Illuminate\Console\Command;

class MigrateExpiringShopifyTokens extends Command
{
    protected $signature = 'shopify:migrate-expiring-tokens
                            {--shop= : Limit to one shop domain}
                            {--dry-run : Show rows without calling Shopify}';

    protected $description = 'Cycle shops from a legacy offline token to an expiring token + refresh_token. Irreversible per shop.';

    public function handle(): int
    {
        $shopFilter = $this->normalizeShop((string) $this->option('shop'));
        $sessions = $this->sessions($shopFilter);
        if ($sessions->isEmpty()) {
            $this->error($shopFilter !== '' ? "No access_token row for {$shopFilter}" : 'No installed sessions with an access_token.');

            return 1;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->line('artisan client_id=' . env('SHOPIFY_API_KEY'));
        $this->line('Mode: ' . ($dryRun ? 'DRY-RUN' : 'LIVE'));
        $this->info('Shops: ' . $sessions->count());
        $this->newLine();

        $migrated = 0;
        $pending = 0;
        $skipped = 0;
        $failed = 0;
        $tokens = new ShopifyTokenService();

        foreach ($sessions as $session) {
            $already = $session->refresh_token && ($session->token_kind ?? '') === 'expiring';
            $this->line("shop={$session->shop} id={$session->id} token_kind=" . ($session->token_kind ?? 'legacy') . ' has_refresh=' . ($session->refresh_token ? 'yes' : 'no') . ' token_len=' . strlen((string) $session->access_token));

            if ($already) {
                $skipped++;
                $this->info('  SKIP already expiring');
                continue;
            }

            if ($dryRun) {
                $pending++;
                $this->info('  Would call Shopify token exchange (expiring=1).');
                continue;
            }

            $this->warn('  Shopify will revoke the current non-expiring token if this succeeds.');
            try {
                $tokens->migrateToExpiring($session->shop);
                $migrated++;
                $this->info('  OK. Stored expiring access_token + refresh_token.');
            } catch (\Throwable $e) {
                if ($this->isUnavailableShop($e)) {
                    $skipped++;
                    $this->warn('  SKIP unavailable shop (closed/frozen). Token left unchanged.');
                    continue;
                }
                $failed++;
                $this->error('  FAILED ' . $e->getMessage());
                $this->warn('  HTTP/exchange failures leave the current access_token in place. Persist failures after a 200 need a reinstall.');
            }
        }

        $this->newLine();
        $this->info("migrated={$migrated} pending={$pending} skipped={$skipped} failed={$failed}");

        return $failed > 0 ? 1 : 0;
    }

    private function sessions(string $shopFilter)
    {
        $query = Session::query()
            ->whereNotNull('shop')
            ->where('shop', '!=', '')
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '');

        if ($shopFilter !== '') {
            $query->where(function ($q) use ($shopFilter) {
                $q->where('shop', $shopFilter)
                    ->orWhere('session_id', $shopFilter)
                    ->orWhere('session_id', 'offline_' . $shopFilter);
            });
        }

        return $query->orderBy('id')
            ->get()
            ->unique('shop')
            ->values();
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

    private function isUnavailableShop(\Throwable $e): bool
    {
        return strpos($e->getMessage(), 'Unavailable Shop') !== false;
    }
}
