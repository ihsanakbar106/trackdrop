<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Official Google Cloud Translation API (Basic / v2).
 * Free tier: first ~500,000 characters / month (requires Cloud project + billing + API enabled).
 * Key: GOOGLE_TRANSLATE_API_KEY in .env — never hardcode.
 */
class GoogleCloudTranslationService
{
    public const COOLDOWN_CACHE_KEY = 'google_translate_api_cooldown';

    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): ?string
    {
        try {
            $apiKey = config('services.google_translate.key');
            $text = trim($text);
            if (empty($apiKey) || $text === '') {
                return null;
            }

            // Tracking statuses are short; refuse huge payloads (timeout / cost risk).
            if (mb_strlen($text) > 2000) {
                $text = mb_substr($text, 0, 2000);
            }

            if (Cache::has(self::COOLDOWN_CACHE_KEY)) {
                Log::warning('GoogleCloudTranslationService: skipped (429 cooldown active)');
                return null;
            }

            $target = strtolower(trim($targetLanguage));
            $source = strtolower(trim($sourceLanguage));
            // BCP-47-ish: he, en, zh-CN — reject garbage so API is never called with junk.
            if ($target === '' || $source === ''
                || !preg_match('/^[a-z]{2}(-[a-z0-9]{2,8})?$/i', $target)
                || !preg_match('/^[a-z]{2}(-[a-z0-9]{2,8})?$/i', $source)
            ) {
                return null;
            }

            // Note: older Laravel HTTP client has no connectTimeout() — do not call it.
            $response = Http::timeout(8)
                ->acceptJson()
                ->asJson()
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->post('https://translation.googleapis.com/language/translate/v2', [
                    'q' => $text,
                    'source' => $source,
                    'target' => $target,
                    'format' => 'text',
                ]);

            if ($response->status() === 429) {
                Cache::put(self::COOLDOWN_CACHE_KEY, 1, now()->addMinutes(5));
                Log::warning('GoogleCloudTranslationService: 429 Too Many Requests — cooldown 5 min');
                return null;
            }

            if (!$response->successful()) {
                Log::warning('GoogleCloudTranslationService: API error', [
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 400),
                ]);
                return null;
            }

            $translated = data_get($response->json(), 'data.translations.0.translatedText');
            if (!is_string($translated) || trim($translated) === '') {
                Log::warning('GoogleCloudTranslationService: empty translation payload');
                return null;
            }

            // API sometimes returns HTML entities (e.g. &#39;).
            $translated = html_entity_decode($translated, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $translated = trim($translated);
            if ($translated === '') {
                return null;
            }

            Log::info('GoogleCloudTranslationService: success', [
                'lang' => $target,
                'preview' => mb_substr($translated, 0, 80),
            ]);

            return $translated;
        } catch (\Throwable $e) {
            // Network / JSON / cache — never throw to callers (tracking page must stay up).
            Log::warning('GoogleCloudTranslationService: request failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
