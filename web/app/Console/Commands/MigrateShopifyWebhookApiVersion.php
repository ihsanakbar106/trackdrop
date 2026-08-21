<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Services\ShopifyTokenService;
use Illuminate\Console\Command;
use RuntimeException;
use Shopify\Clients\Graphql;
use Shopify\Context;
use Throwable;

class MigrateShopifyWebhookApiVersion extends Command
{
    private const TARGET_VERSION = '2026-07';
    private const PAGE_SIZE = 100;

    protected $signature = 'shopify:migrate-webhooks
                            {--shop= : Limit to one shop domain, e.g. store.myshopify.com}
                            {--dry-run : Discover and log without deleting or creating subscriptions}';

    protected $description = 'One-time migrate shop-specific webhook payload versions to 2026-07 (delete + recreate same topic and URI).';

    /** @var ShopifyTokenService */
    private $tokens;

    private $skipped = 0;
    private $wouldMigrate = 0;
    private $migrated = 0;
    private $failed = 0;

    public function handle(): int
    {
        $this->tokens = new ShopifyTokenService();

        if (Context::$API_VERSION !== self::TARGET_VERSION) {
            $this->error('Refusing to run: Context API version is ' . (string) Context::$API_VERSION . ', expected ' . self::TARGET_VERSION . '.');

            return 1;
        }

        $dryRun = (bool) $this->option('dry-run');
        $shopFilter = $this->normalizeShop((string) $this->option('shop'));

        $this->line('Target webhook payload version: ' . self::TARGET_VERSION);
        $this->line('GraphQL path: admin/api/' . Context::$API_VERSION . '/graphql.json');
        $this->line('Mode: ' . ($dryRun ? 'DRY-RUN (no remote writes)' : 'LIVE'));
        $this->newLine();

        $shops = $this->installedShops($shopFilter);
        if ($shops === []) {
            $this->error($shopFilter !== '' ? "No installed session with an access_token for {$shopFilter}." : 'No installed sessions with an access_token.');

            return 1;
        }

        $this->info('Shops to inspect: ' . count($shops));
        $this->newLine();

        foreach ($shops as $shop) {
            try {
                $this->processShop($shop, $dryRun);
            } catch (Throwable $e) {
                $this->failed++;
                $this->error("SHOP: {$shop}");
                $this->error('ACTION: FAILED (shop)');
                $this->error($e->getMessage());
                $this->newLine();
            }
        }

        $this->newLine();
        $this->info('Summary');
        $this->line('  shops: ' . count($shops));
        $this->line('  skipped: ' . $this->skipped);
        if ($dryRun) {
            $this->line('  would migrate: ' . $this->wouldMigrate);
        } else {
            $this->line('  migrated: ' . $this->migrated);
        }
        $this->line('  failed: ' . $this->failed);

        if ($this->failed > 0) {
            $this->error('Migration finished with failures. Repair FAILED rows manually (same topic + URI on 2026-07).');

            return 1;
        }

        $this->info($dryRun ? 'Dry-run complete. No subscriptions were changed.' : 'Migration complete.');

        return 0;
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

    private function processShop(string $shop, bool $dryRun): void
    {
        $token = $this->tokens->getValidAccessToken($shop);
        $client = new Graphql($shop, $token);
        $subscriptions = $this->listSubscriptions($client);

        $this->line("SHOP: {$shop}");
        $this->line('SUBSCRIPTIONS: ' . count($subscriptions));

        foreach ($subscriptions as $subscription) {
            $this->processSubscription($client, $shop, $subscription, $subscriptions, $dryRun);
        }

        $this->newLine();
    }

    /**
     * @param array<int, array<string, mixed>> $subscriptions
     */
    private function processSubscription(Graphql $client, string $shop, array $subscription, array &$subscriptions, bool $dryRun): void
    {
        $id = (string) ($subscription['id'] ?? '');
        $topic = (string) ($subscription['topic'] ?? '');
        $uri = (string) ($subscription['uri'] ?? '');
        $version = (string) ($subscription['apiVersion'] ?? '');

        if ($version === self::TARGET_VERSION) {
            $this->skipped++;
            $this->logSubscription($shop, $topic, $version, $uri, 'SKIP');

            return;
        }

        if ($id === '' || $topic === '' || $uri === '') {
            $this->skipped++;
            $this->logSubscription($shop, $topic !== '' ? $topic : '(missing)', $version !== '' ? $version : '(missing)', $uri !== '' ? $uri : '(missing)', 'SKIP (invalid topic or URI)');

            return;
        }

        if (!$this->isOlderThanTarget($version)) {
            $this->skipped++;
            $this->logSubscription($shop, $topic, $version !== '' ? $version : '(missing)', $uri, 'SKIP (not older than ' . self::TARGET_VERSION . ')');

            return;
        }

        $equivalent = $this->findEquivalentTargetVersion($subscriptions, $topic, $uri, $id);
        $action = $equivalent ? 'DELETE old only (2026-07 equivalent already exists)' : 'DELETE + CREATE';

        $this->logSubscription($shop, $topic, $version, $uri, $dryRun ? 'DRY-RUN ' . $action : $action, $id);

        if ($dryRun) {
            $this->wouldMigrate++;

            return;
        }

        try {
            $this->deleteSubscription($client, $id);
        } catch (Throwable $e) {
            $this->markFailed($shop, $topic, $uri, $id, 'delete failed: ' . $e->getMessage());

            return;
        }

        if ($equivalent) {
            $this->migrated++;
            $this->info("OK deleted old subscription; kept existing 2026-07 {$equivalent['id']}");

            return;
        }

        try {
            $created = $this->createSubscription($client, $subscription);
            $subscriptions[] = $created;
            $this->migrated++;
            $this->info('OK created ' . $created['id'] . ' apiVersion=' . $created['apiVersion']);
        } catch (Throwable $e) {
            $this->markFailed(
                $shop,
                $topic,
                $uri,
                $id,
                'delete succeeded, create failed: ' . $e->getMessage() . ' — recreate manually with the same topic and URI'
            );
        }
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
                throw new RuntimeException('webhookSubscriptions response missing data.webhookSubscriptions');
            }

            foreach ($connection['nodes'] ?? [] as $node) {
                if (!is_array($node)) {
                    continue;
                }
                $items[] = $this->normalizeNode($node);
            }

            $pageInfo = $connection['pageInfo'] ?? [];
            $hasNext = !empty($pageInfo['hasNextPage']);
            $cursor = $hasNext ? ($pageInfo['endCursor'] ?? null) : null;
        } while ($hasNext && $cursor);

        return $items;
    }

    /**
     * @param array<string, mixed> $node
     * @return array<string, mixed>
     */
    private function normalizeNode(array $node): array
    {
        $includeFields = $node['includeFields'] ?? [];
        $namespaces = $node['metafieldNamespaces'] ?? [];

        return [
            'id' => (string) ($node['id'] ?? ''),
            'topic' => (string) ($node['topic'] ?? ''),
            'uri' => (string) ($node['uri'] ?? ''),
            'apiVersion' => (string) data_get($node, 'apiVersion.handle', ''),
            'filter' => isset($node['filter']) ? (string) $node['filter'] : '',
            'format' => isset($node['format']) ? (string) $node['format'] : '',
            'name' => isset($node['name']) ? (string) $node['name'] : '',
            'includeFields' => is_array($includeFields) ? array_values($includeFields) : [],
            'metafieldNamespaces' => is_array($namespaces) ? array_values($namespaces) : [],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $subscriptions
     * @return array<string, mixed>|null
     */
    private function findEquivalentTargetVersion(array $subscriptions, string $topic, string $uri, string $excludeId): ?array
    {
        foreach ($subscriptions as $candidate) {
            if (($candidate['id'] ?? '') === $excludeId) {
                continue;
            }
            if (($candidate['apiVersion'] ?? '') !== self::TARGET_VERSION) {
                continue;
            }
            if (($candidate['topic'] ?? '') === $topic && ($candidate['uri'] ?? '') === $uri) {
                return $candidate;
            }
        }

        return null;
    }

    private function deleteSubscription(Graphql $client, string $id): void
    {
        $body = $this->graphql($client, $this->deleteMutation(), ['id' => $id]);
        $payload = $body['data']['webhookSubscriptionDelete'] ?? null;
        if (!is_array($payload)) {
            throw new RuntimeException('webhookSubscriptionDelete returned no payload');
        }
        $this->assertNoUserErrors($payload['userErrors'] ?? [], 'webhookSubscriptionDelete');
        if (empty($payload['deletedWebhookSubscriptionId'])) {
            throw new RuntimeException('webhookSubscriptionDelete did not return deletedWebhookSubscriptionId');
        }
    }

    /**
     * @param array<string, mixed> $subscription
     * @return array<string, mixed>
     */
    private function createSubscription(Graphql $client, array $subscription): array
    {
        $input = ['uri' => $subscription['uri']];
        if ($subscription['filter'] !== '') {
            $input['filter'] = $subscription['filter'];
        }
        if ($subscription['format'] !== '') {
            $input['format'] = $subscription['format'];
        }
        if ($subscription['name'] !== '') {
            $input['name'] = $subscription['name'];
        }
        if ($subscription['includeFields'] !== []) {
            $input['includeFields'] = $subscription['includeFields'];
        }
        if ($subscription['metafieldNamespaces'] !== []) {
            $input['metafieldNamespaces'] = $subscription['metafieldNamespaces'];
        }

        $body = $this->graphql($client, $this->createMutation(), [
            'topic' => $subscription['topic'],
            'webhookSubscription' => $input,
        ]);
        $payload = $body['data']['webhookSubscriptionCreate'] ?? null;
        if (!is_array($payload)) {
            throw new RuntimeException('webhookSubscriptionCreate returned no payload');
        }
        $this->assertNoUserErrors($payload['userErrors'] ?? [], 'webhookSubscriptionCreate');
        $created = $payload['webhookSubscription'] ?? null;
        if (!is_array($created) || empty($created['id'])) {
            throw new RuntimeException('webhookSubscriptionCreate did not return a subscription id');
        }

        $createdVersion = (string) data_get($created, 'apiVersion.handle', '');
        if ($createdVersion !== self::TARGET_VERSION) {
            throw new RuntimeException('Created subscription apiVersion.handle is ' . ($createdVersion !== '' ? $createdVersion : '(missing)') . ', expected ' . self::TARGET_VERSION);
        }

        return $this->normalizeNode($created);
    }

    /**
     * @param array<string, mixed> $variables
     * @return array<string, mixed>
     */
    private function graphql(Graphql $client, string $query, array $variables = []): array
    {
        try {
            $response = $client->query([
                'query' => $query,
                'variables' => $variables,
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException('GraphQL transport error: ' . $e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();
        if ($status !== 200) {
            throw new RuntimeException('GraphQL HTTP ' . $status);
        }

        try {
            $body = $response->getDecodedBody();
        } catch (Throwable $e) {
            throw new RuntimeException('GraphQL response was not valid JSON: ' . $e->getMessage(), 0, $e);
        }

        if (!is_array($body)) {
            throw new RuntimeException('GraphQL response body was empty');
        }
        if (!empty($body['errors'])) {
            throw new RuntimeException('GraphQL errors: ' . json_encode($body['errors']));
        }

        return $body;
    }

    /**
     * @param mixed $userErrors
     */
    private function assertNoUserErrors($userErrors, string $operation): void
    {
        if (!is_array($userErrors) || $userErrors === []) {
            return;
        }

        $parts = [];
        foreach ($userErrors as $error) {
            if (!is_array($error)) {
                continue;
            }
            $field = isset($error['field']) ? json_encode($error['field']) : 'null';
            $parts[] = $field . ': ' . (string) ($error['message'] ?? '');
        }

        throw new RuntimeException($operation . ' userErrors: ' . implode('; ', $parts));
    }

    private function isOlderThanTarget(string $handle): bool
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $handle)) {
            return false;
        }

        return $handle < self::TARGET_VERSION;
    }

    private function markFailed(string $shop, string $topic, string $uri, string $id, string $reason): void
    {
        $this->failed++;
        $this->error("SHOP: {$shop}");
        $this->error("TOPIC: {$topic}");
        $this->error("URI: {$uri}");
        $this->error("ID: {$id}");
        $this->error('ACTION: FAILED');
        $this->error($reason);
    }

    private function logSubscription(string $shop, string $topic, string $version, string $uri, string $action, string $id = ''): void
    {
        $this->line("SHOP: {$shop}");
        $this->line("TOPIC: {$topic}");
        if (strpos($action, 'SKIP') === 0 && $version === self::TARGET_VERSION) {
            $this->line("VERSION: {$version}");
        } else {
            $this->line("CURRENT VERSION: {$version}");
            $this->line('TARGET VERSION: ' . self::TARGET_VERSION);
        }
        $this->line("URI: {$uri}");
        if ($id !== '') {
            $this->line("ID: {$id}");
        }
        $this->line("ACTION: {$action}");
        $this->newLine();
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

    private function listQuery(): string
    {
        return <<<'GRAPHQL'
query webhookSubscriptions($first: Int!, $after: String) {
  webhookSubscriptions(first: $first, after: $after) {
    nodes {
      id
      topic
      uri
      apiVersion {
        handle
      }
      filter
      format
      includeFields
      metafieldNamespaces
      name
    }
    pageInfo {
      hasNextPage
      endCursor
    }
  }
}
GRAPHQL;
    }

    private function deleteMutation(): string
    {
        return <<<'GRAPHQL'
mutation webhookSubscriptionDelete($id: ID!) {
  webhookSubscriptionDelete(id: $id) {
    deletedWebhookSubscriptionId
    userErrors {
      field
      message
    }
  }
}
GRAPHQL;
    }

    private function createMutation(): string
    {
        return <<<'GRAPHQL'
mutation webhookSubscriptionCreate($topic: WebhookSubscriptionTopic!, $webhookSubscription: WebhookSubscriptionInput!) {
  webhookSubscriptionCreate(topic: $topic, webhookSubscription: $webhookSubscription) {
    userErrors {
      field
      message
    }
    webhookSubscription {
      id
      topic
      uri
      apiVersion {
        handle
      }
      filter
      format
      includeFields
      metafieldNamespaces
      name
    }
  }
}
GRAPHQL;
    }
}
