<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Official Google Generative Language (Gemini) API for short tracking-status translation.
 * Key must live in .env — never hardcode.
 */
class GeminiTranslationService
{
    public const COOLDOWN_CACHE_KEY = 'gemini_translate_cooldown';

    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): ?string
    {
        try {
            $apiKey = config('services.google_generative_ai.key');
            if (empty($apiKey) || trim($text) === '') {
                return null;
            }

            if (Cache::has(self::COOLDOWN_CACHE_KEY)) {
                Log::warning('GeminiTranslationService: skipped (429 cooldown active)');
                return null;
            }

            $targetName = $this->languageName($targetLanguage);
            $sourceName = $this->languageName($sourceLanguage);
            $prompt = "Translate the following text from {$sourceName} to {$targetName}. "
                . "Return ONLY the translated text. No quotes, no explanation, no extra words.\n\n"
                . $text;

            foreach ($this->modelCandidates() as $model) {
                $result = $this->requestTranslation($apiKey, $model, $prompt);
                if ($result['status'] === 429) {
                    Cache::put(self::COOLDOWN_CACHE_KEY, 1, now()->addMinutes(5));
                    Log::warning('GeminiTranslationService: 429 Too Many Requests — cooldown 5 min', [
                        'model' => $model,
                    ]);
                    return null;
                }
                if ($result['status'] === 404) {
                    Log::warning('GeminiTranslationService: model not found, trying next', [
                        'model' => $model,
                    ]);
                    continue;
                }
                if ($result['text'] !== null) {
                    Log::info('GeminiTranslationService: success', [
                        'model' => $model,
                        'preview' => mb_substr($result['text'], 0, 80),
                    ]);
                    return $result['text'];
                }
                Log::warning('GeminiTranslationService: API error', [
                    'model' => $model,
                    'status' => $result['status'],
                    'body' => $result['body'],
                ]);
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('GeminiTranslationService: request failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Preferred model first, then stable fallbacks (404 on one model ≠ key failure).
     *
     * @return list<string>
     */
    protected function modelCandidates(): array
    {
        $preferred = (string) config('services.google_generative_ai.model', 'gemini-2.5-flash');
        $fallbacks = [
            'gemini-2.5-flash',
            'gemini-flash-latest',
            'gemini-2.0-flash',
            'gemini-1.5-flash',
            'gemini-1.5-flash-latest',
        ];

        return array_values(array_unique(array_filter(array_merge([$preferred], $fallbacks))));
    }

    /**
     * @return array{status:int|null,text:?string,body:?string}
     */
    protected function requestTranslation(string $apiKey, string $model, string $prompt): array
    {
        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            rawurlencode($model)
        );

        $response = Http::timeout(15)
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
            ->acceptJson()
            ->asJson()
            ->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 256,
                ],
            ]);

        $status = $response->status();
        if ($status === 429 || $status === 404) {
            return [
                'status' => $status,
                'text' => null,
                'body' => mb_substr($response->body(), 0, 300),
            ];
        }

        if (!$response->successful()) {
            return [
                'status' => $status,
                'text' => null,
                'body' => mb_substr($response->body(), 0, 300),
            ];
        }

        $translated = data_get($response->json(), 'candidates.0.content.parts.0.text');
        if (!is_string($translated) || trim($translated) === '') {
            return [
                'status' => $status,
                'text' => null,
                'body' => 'empty candidates',
            ];
        }

        return [
            'status' => $status,
            'text' => trim($translated, " \t\n\r\0\x0B\"'"),
            'body' => null,
        ];
    }

    protected function languageName(string $code): string
    {
        $map = [
            'en' => 'English',
            'he' => 'Hebrew',
            'ar' => 'Arabic',
            'fr' => 'French',
            'es' => 'Spanish',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'nl' => 'Dutch',
            'pl' => 'Polish',
            'tr' => 'Turkish',
            'hi' => 'Hindi',
        ];

        $code = strtolower(trim($code));

        return $map[$code] ?? $code;
    }
}
