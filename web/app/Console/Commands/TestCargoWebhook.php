<?php

namespace App\Console\Commands;

use App\Models\Fulfillment;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TestCargoWebhook extends Command
{
    protected $signature = 'cargo:test-webhook
        {tracking? : Tracking / shipment id to update (default 69837773)}
        {--url= : Webhook base URL (default APP_URL or https://app.theautotrack.com)}
        {--status-code=25 : Cargo status_code to send}
        {--event= : Unique latest event text (default WEBHOOK_TEST_<timestamp>)}
        {--skip-db : Only check HTTP response, do not assert local/prod DB}';

    protected $description = 'POST a test payload to Cargo status webhook and optionally verify DB update';

    public function handle(): int
    {
        $tracking = (string) ($this->argument('tracking') ?: '69837773');
        $statusCode = (int) $this->option('status-code');
        $event = $this->option('event') ?: ('WEBHOOK_TEST_' . gmdate('YmdHis'));
        $secret = (string) env('CARGO_WEBHOOK_SECRET', '');

        if ($secret === '') {
            $this->error('CARGO_WEBHOOK_SECRET missing in .env');

            return 1;
        }

        $base = rtrim((string) ($this->option('url') ?: env('APP_URL') ?: 'https://app.theautotrack.com'), '/');
        if ($base === '' || !preg_match('#^https?://#i', $base)) {
            $base = 'https://app.theautotrack.com';
        }
        $endpoint = $base . '/api/webhooks/cargo-status-update?secret=' . urlencode($secret);

        $before = null;
        if (!$this->option('skip-db')) {
            $before = Fulfillment::where('tracking_number', $tracking)
                ->orWhere('shipment_id', $tracking)
                ->orderByDesc('updated_at')
                ->first();
            if ($before) {
                $this->info('DB before: ' . json_encode([
                    'tracking_number' => $before->tracking_number,
                    'shipment_status' => $before->shipment_status,
                    'shipment_last_event' => $before->shipment_last_event,
                    'updated_at' => (string) $before->updated_at,
                ], JSON_UNESCAPED_UNICODE));
            } else {
                $this->warn("No fulfillment for {$tracking} in THIS database — HTTP will still be tested; DB assert skipped.");
            }
        }

        $payload = [
            'shipment_id' => is_numeric($tracking) ? (int) $tracking : $tracking,
            'status_code' => $statusCode,
            'status_text_en' => $event,
            'status_text' => $event,
            'status_date' => gmdate('Y-m-d H:i:s'),
            'city' => 'Israel',
            'state' => 'Israel',
        ];

        $this->line('POST ' . preg_replace('#secret=[^&]+#', 'secret=***', $endpoint));
        $this->line('Payload: ' . json_encode($payload, JSON_UNESCAPED_UNICODE));

        try {
            $client = new Client(['http_errors' => false, 'timeout' => 30]);
            $response = $client->request('POST', $endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Cargo-Webhook-Secret' => $secret,
                ],
                'json' => $payload,
            ]);
            $http = $response->getStatusCode();
            $body = (string) $response->getBody();
        } catch (\Throwable $e) {
            $this->error('Request failed: ' . $e->getMessage());

            return 1;
        }

        $this->line("HTTP: {$http}");
        $this->line('Body: ' . $body);

        $decoded = json_decode($body, true);
        $ok = $http === 200 && is_array($decoded) && (($decoded['status'] ?? null) === 'success');

        if (!$ok) {
            $this->error('FAIL: webhook did not return status=success');

            return 1;
        }

        $this->info('PASS: webhook HTTP success');

        if ($this->option('skip-db') || !$before) {
            $this->warn('DB assert skipped (no local row or --skip-db). Check prod UI/DB for event: ' . $event);

            return 0;
        }

        sleep(1);
        $after = Fulfillment::where('id', $before->id)->first();
        $this->info('DB after: ' . json_encode([
            'shipment_status' => $after->shipment_status,
            'shipment_last_event' => $after->shipment_last_event,
            'shipment_substatus' => $after->shipment_substatus,
            'updated_at' => (string) $after->updated_at,
        ], JSON_UNESCAPED_UNICODE));

        if ((string) $after->shipment_last_event !== $event) {
            $this->error("FAIL: expected shipment_last_event={$event}");

            return 1;
        }

        $this->info('PASS: DB updated by webhook (unique event matched)');

        return 0;
    }
}
