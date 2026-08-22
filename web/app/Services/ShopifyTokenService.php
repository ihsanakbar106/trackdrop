<?php

namespace App\Services;

use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ShopifyTokenService
{
    // ponytail: 300s leeway is hardcoded; lift to config if Shopify shortens expires_in
    public const LEEWAY_SECONDS = 300;

    public function getValidAccessToken(string $shopDomain): string
    {
        $session = Session::where('shop', $shopDomain)->first();
        if (!$session || $session->access_token === null || $session->access_token === '') {
            throw new RuntimeException('No Shopify access token for shop');
        }
        if (!self::needsRefresh($session->refresh_token, $session->token_kind ?? 'legacy', $session->access_token_expires_at)) {
            return $session->access_token;
        }

        return $this->refresh($session);
    }

    public static function needsRefresh(?string $refreshToken, ?string $kind, $expiresAt): bool
    {
        if ($refreshToken === null || $refreshToken === '' || $kind !== 'expiring' || $expiresAt === null || $expiresAt === '') {
            return false;
        }

        return Carbon::parse($expiresAt)->subSeconds(self::LEEWAY_SECONDS)->isPast();
    }

    private function refresh(Session $session): string
    {
        return DB::transaction(function () use ($session) {
            $row = Session::where('id', $session->id)->lockForUpdate()->first();
            if (!self::needsRefresh($row->refresh_token, $row->token_kind, $row->access_token_expires_at)) {
                return $row->access_token;
            }

            $response = Http::asForm()->acceptJson()->post('https://' . $row->shop . '/admin/oauth/access_token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => Crypt::decryptString($row->refresh_token),
                'client_id' => env('SHOPIFY_API_KEY'),
                'client_secret' => env('SHOPIFY_API_SECRET'),
            ]);

            if (!$response->ok()) {
                throw new RuntimeException($this->httpError('refresh', $response));
            }

            return $this->persistExpiring($row, $response->json() ?: []);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function exchangeAuthorizationCode(string $shop, string $code): array
    {
        $base = [
            'client_id' => env('SHOPIFY_API_KEY'),
            'client_secret' => env('SHOPIFY_API_SECRET'),
            'code' => $code,
        ];

        $response = Http::asForm()->acceptJson()->post(
            'https://' . $shop . '/admin/oauth/access_token',
            $base + ['expiring' => '1']
        );

        if (!$response->ok()) {
            Log::warning('Expiring OAuth exchange failed; retrying without expiring=1', [
                'shop' => $shop,
                'status' => $response->status(),
            ]);
            $response = Http::asForm()->acceptJson()->post(
                'https://' . $shop . '/admin/oauth/access_token',
                $base
            );
        }

        if (!$response->ok()) {
            throw new RuntimeException($this->httpError('oauth', $response));
        }

        $body = $response->json() ?: [];
        if (empty($body['access_token'])) {
            throw new RuntimeException('Shopify token oauth failed: missing access_token');
        }

        return $body;
    }

    public function persistFromAuthorizationResponse(string $sessionId, array $data): void
    {
        $session = Session::where('session_id', $sessionId)->first();
        if (!$session) {
            throw new RuntimeException('No session after OAuth store');
        }

        if (!empty($data['refresh_token'])) {
            $this->persistExpiring($session, $data);

            return;
        }

        $this->persistLegacy($session, $data);
    }

    /**
     * Irreversible: Shopify retires the non-expiring token in the same call.
     */
    public function migrateToExpiring(string $shopDomain): string
    {
        $session = Session::where('shop', $shopDomain)->first();
        if (!$session || $session->access_token === null || $session->access_token === '') {
            throw new RuntimeException('No Shopify access token for shop');
        }
        if ($session->refresh_token && ($session->token_kind ?? '') === 'expiring') {
            return $session->access_token;
        }

        return DB::transaction(function () use ($session) {
            $row = Session::where('id', $session->id)->lockForUpdate()->first();
            if ($row->refresh_token && $row->token_kind === 'expiring') {
                return $row->access_token;
            }

            $response = Http::asForm()->acceptJson()->post('https://' . $row->shop . '/admin/oauth/access_token', [
                'client_id' => env('SHOPIFY_API_KEY'),
                'client_secret' => env('SHOPIFY_API_SECRET'),
                'grant_type' => 'urn:ietf:params:oauth:grant-type:token-exchange',
                'subject_token' => $row->access_token,
                'subject_token_type' => 'urn:shopify:params:oauth:token-type:offline-access-token',
                'requested_token_type' => 'urn:shopify:params:oauth:token-type:offline-access-token',
                'expiring' => '1',
            ]);

            if (!$response->ok()) {
                throw new RuntimeException($this->httpError('migrate', $response));
            }

            return $this->persistExpiring($row, $response->json() ?: []);
        });
    }

    private function persistExpiring(Session $row, array $data): string
    {
        $access = $data['access_token'] ?? null;
        $refresh = $data['refresh_token'] ?? null;
        if (!$access || !$refresh) {
            throw new RuntimeException('Shopify token response missing access or refresh token');
        }

        $row->access_token = $access;
        $row->refresh_token = Crypt::encryptString($refresh);
        $row->access_token_expires_at = Carbon::now()->addSeconds((int) ($data['expires_in'] ?? 3600));
        if (isset($data['refresh_token_expires_in'])) {
            $row->refresh_token_expires_at = Carbon::now()->addSeconds((int) $data['refresh_token_expires_in']);
        }
        $row->token_kind = 'expiring';
        $row->save();

        return $row->access_token;
    }

    private function persistLegacy(Session $row, array $data): void
    {
        $access = $data['access_token'] ?? null;
        if (!$access) {
            throw new RuntimeException('Shopify token oauth failed: missing access_token');
        }

        $row->access_token = $access;
        $row->refresh_token = null;
        $row->access_token_expires_at = null;
        $row->refresh_token_expires_at = null;
        $row->token_kind = 'legacy';
        $row->save();
    }

    private function httpError(string $action, $response): string
    {
        $json = $response->json();
        $detail = is_array($json)
            ? json_encode($json)
            : substr((string) $response->body(), 0, 300);

        return 'Shopify token ' . $action . ' failed: ' . $response->status() . ' ' . $detail;
    }
}
