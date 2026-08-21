<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Services\ShopifyTokenService;
use Illuminate\Console\Command;
use RuntimeException;
use Shopify\Clients\Graphql;
use Throwable;

class UpdateShopifyWebhookApiVersion extends Command
{
    private const TARGET_VERSION = '2026-07';
    private const PAGE_SIZE = 10;

    protected $signature = 'shopify:update-webhook-version
                            {--shop= : Limit to one shop domain}
                            {--dry-run : List subscriptions without calling webhookSubscriptionUpdate}';

    protected $description = 'Set existing shop webhook subscriptions to apiVersion 2026-07 via webhookSubscriptionUpdate.';

    /** @var ShopifyTokenService */
    private $tokens;

    private $updated = 0;
    private $skipped = 0;
    private $failed = 0;

    public function handle(): int
    {
        $this->tokens = new ShopifyTokenService();
        $dryRun = (bool) $this->option('dry-run');
        $shopFilter = $this->normalizeShop((string) $this->option('shop'));
        $shops = $this->installedShops($shopFilter);

        if ($shops === []) {
            $this->error($shopFilter !== '' ? "No installed session with an access_token for {$shopFilter}." : 'No installed sessions with an access_token.');

            return 1;
        }

        $this->line('Target: ' . self::TARGET_VERSION);
        $this->line('Mode: ' . ($dryRun ? 'DRY-RUN' : 'LIVE'));
        $this->info('Shops: ' . count($shops));
        $this->newLine();

        foreach ($shops as $shop) {
            try {
                $this->updateShop($shop, $dryRun);
            } catch (Throwable $e) {
                if ($this->isUnusableToken($e)) {
                    $this->skipped++;
                    $this->warn("SHOP: {$shop} SKIP (no usable token) " . $e->getMessage());
                    continue;
                }
                $this->failed++;
                $this->error("SHOP: {$shop} FAILED " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('updated=' . $this->updated . ' skipped=' . $this->skipped . ' failed=' . $this->failed);

        return $this->failed > 0 ? 1 : 0;
    }

    private function updateShop(string $shop, bool $dryRun): void
    {
        $client = new Graphql($shop, $this->tokens->getValidAccessToken($shop));
        $subscriptions = $this->listSubscriptions($client);

        $this->line("SHOP: {$shop} SUBSCRIPTIONS: " . count($subscriptions));

        foreach ($subscriptions as $subscription) {
            $id = (string) ($subscription['id'] ?? '');
            $topic = (string) ($subscription['topic'] ?? '');
            $uri = (string) ($subscription['uri'] ?? '');
            $version = (string) ($subscription['apiVersion'] ?? '');

            if ($id === '') {
                $this->skipped++;
                $this->warn("  SKIP missing id topic={$topic}");
                continue;
            }

            if ($version === self::TARGET_VERSION) {
                $this->skipped++;
                $this->line("  SKIP {$topic} already " . self::TARGET_VERSION);
                continue;
            }

            $this->line("  {$topic} {$version} -> " . self::TARGET_VERSION . " {$id}");
            $this->line("  {$uri}");

            if ($dryRun) {
                $this->skipped++;
                continue;
            }

            try {
                $assigned = $this->updateSubscription($client, $id);
                if ($assigned !== self::TARGET_VERSION) {
                    $this->failed++;
                    $this->error("  FAILED {$id} still {$assigned}");
                    continue;
                }
                $this->updated++;
                $this->info("  OK {$id} apiVersion={$assigned}");
            } catch (Throwable $e) {
                $this->failed++;
                $this->error("  FAILED {$id} " . $e->getMessage());
            }
        }

        $this->newLine();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function listSubscriptions(Graphql $client): array
    {
        $items = [];
        $cursor = null;

        do {
            $body = $this->graphql($client, $this->listQuery(), [
                'first' => self::PAGE_SIZE,
                'after' => $cursor,
            ]);
            $connection = $body['data']['webhookSubscriptions'] ?? null;
            if (!is_array($connection)) {
                throw new RuntimeException('webhookSubscriptions response missing');
            }

            foreach ($connection['edges'] ?? [] as $edge) {
                $node = is_array($edge) ? ($edge['node'] ?? null) : null;
                if (!is_array($node) || empty($node['id'])) {
                    continue;
                }
                $items[] = [
                    'id' => (string) $node['id'],
                    'topic' => (string) ($node['topic'] ?? ''),
                    'uri' => (string) ($node['uri'] ?? ''),
                    'apiVersion' => (string) data_get($node, 'apiVersion.handle', $node['apiVersion'] ?? ''),
                ];
            }

            $pageInfo = $connection['pageInfo'] ?? [];
            $hasNext = !empty($pageInfo['hasNextPage']);
            $cursor = $hasNext ? ($pageInfo['endCursor'] ?? null) : null;
        } while ($hasNext && $cursor);

        return $items;
    }

    private function updateSubscription(Graphql $client, string $id): string
    {
        $body = $this->graphql($client, $this->updateMutation(), [
            'id' => $id,
            'webhookSubscription' => [
                'apiVersion' => self::TARGET_VERSION,
            ],
        ]);
        $payload = $body['data']['webhookSubscriptionUpdate'] ?? null;
        if (!is_array($payload)) {
            throw new RuntimeException('webhookSubscriptionUpdate returned no payload');
        }

        $userErrors = $payload['userErrors'] ?? [];
        if (is_array($userErrors) && $userErrors !== []) {
            throw new RuntimeException('userErrors: ' . json_encode($userErrors));
        }

        $updated = $payload['webhookSubscription'] ?? null;
        if (!is_array($updated)) {
            throw new RuntimeException('webhookSubscriptionUpdate returned no subscription');
        }

        return (string) data_get($updated, 'apiVersion.handle', $updated['apiVersion'] ?? '');
    }

    /**
     * @param array<string, mixed> $variables
     * @return array<string, mixed>
     */
    private function graphql(Graphql $client, string $query, array $variables = []): array
    {
        $response = $client->query([
            'query' => $query,
            'variables' => $variables,
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('GraphQL HTTP ' . $response->getStatusCode());
        }

        $body = $response->getDecodedBody();
        if (!is_array($body)) {
            throw new RuntimeException('GraphQL response body was empty');
        }
        if (!empty($body['errors'])) {
            throw new RuntimeException('GraphQL errors: ' . json_encode($body['errors']));
        }

        return $body;
    }

    /**
     * @return string[]
     */
    private function installedShops(string $shopFilter): array
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
            ->pluck('shop')
            ->unique()
            ->values()
            ->all();
    }

    private function isUnusableToken(Throwable $e): bool
    {
        $message = $e->getMessage();

        return strpos($message, 'refresh_token') !== false
            || strpos($message, 'No Shopify access token') !== false
            || strpos($message, 'invalid_request') !== false;
    }

    private function normalizeShop(string $shop): string
    {
        $shop = strtolower(trim($shop));
        if (strpos($shop, 'offline_') === 0) {
            $shop = substr($shop, strlen('offline_'));
        }
        if ($shop !== '' && strpos($shop, '.') === false) {
            $shop .= '.myshopify.com';
        }

        return $shop;
    }

    private function listQuery(): string
    {
        return <<<'GRAPHQL'
query webhookSubscriptions($first: Int!, $after: String) {
  webhookSubscriptions(first: $first, after: $after) {
    edges {
      node {
        id
        topic
        uri
        apiVersion {
          handle
        }
      }
    }
    pageInfo {
      hasNextPage
      endCursor
    }
  }
}
GRAPHQL;
    }

    private function updateMutation(): string
    {
        return <<<'GRAPHQL'
mutation updateWebhookVersion($id: ID!, $webhookSubscription: WebhookSubscriptionInput!) {
  webhookSubscriptionUpdate(id: $id, webhookSubscription: $webhookSubscription) {
    webhookSubscription {
      id
      topic
      apiVersion {
        handle
      }
    }
    userErrors {
      field
      message
    }
  }
}
GRAPHQL;
    }
}
