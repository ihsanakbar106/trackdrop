<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * One bulk sync pipeline per shop (OrderSync → images → tracking).
 * Prevents duplicate OrderSync / phase-2 / phase-3 runs when multiple queue workers exist.
 */
class ShopSyncLock
{
    public static function key(string $shop): string
    {
        return 'shop_sync_pipeline:' . md5($shop);
    }

    public static function isLocked(string $shop): bool
    {
        return Cache::has(self::key($shop));
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function acquire(string $shop, array $meta = [], int $hours = 6): bool
    {
        $payload = array_merge([
            'shop' => $shop,
            'started_at' => now()->toDateTimeString(),
        ], $meta);

        return Cache::add(self::key($shop), $payload, now()->addHours($hours));
    }

    /**
     * Refresh TTL while a long pipeline is still running.
     */
    public static function touch(string $shop, int $hours = 6): void
    {
        $key = self::key($shop);
        $existing = Cache::get($key);
        if ($existing === null) {
            return;
        }
        if (!is_array($existing)) {
            $existing = ['shop' => $shop];
        }
        $existing['touched_at'] = now()->toDateTimeString();
        Cache::put($key, $existing, now()->addHours($hours));
    }

    public static function release(string $shop, ?string $reason = null): void
    {
        Cache::forget(self::key($shop));
    }
}
