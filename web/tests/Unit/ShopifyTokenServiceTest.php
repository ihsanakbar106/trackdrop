<?php

namespace Tests\Unit;

use App\Services\ShopifyTokenService;
use PHPUnit\Framework\TestCase;

class ShopifyTokenServiceTest extends TestCase
{
    public function test_legacy_rows_never_need_refresh()
    {
        $this->assertFalse(ShopifyTokenService::needsRefresh(null, 'legacy', null));
        $this->assertFalse(ShopifyTokenService::needsRefresh('', 'legacy', '2020-01-01 00:00:00'));
        $this->assertFalse(ShopifyTokenService::needsRefresh(null, 'expiring', '2020-01-01 00:00:00'));
        $this->assertFalse(ShopifyTokenService::needsRefresh('enc', 'legacy', '2020-01-01 00:00:00'));
    }

    public function test_expiring_token_respects_leeway()
    {
        $this->assertTrue(ShopifyTokenService::needsRefresh('enc', 'expiring', date('Y-m-d H:i:s', time() - 60)));
        $this->assertFalse(ShopifyTokenService::needsRefresh('enc', 'expiring', date('Y-m-d H:i:s', time() + 3600)));
    }
}
