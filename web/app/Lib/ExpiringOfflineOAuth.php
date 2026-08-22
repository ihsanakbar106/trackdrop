<?php

namespace App\Lib;

use App\Services\ShopifyTokenService;
use ReflectionClass;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session as AuthSession;
use Shopify\Context;
use Shopify\Exception\CookieSetException;
use Shopify\Exception\InvalidOAuthException;
use Shopify\Exception\SessionStorageException;
use Shopify\Utils;

/**
 * OAuth callback with expiring=1. SDK OAuth::callback() omits that flag.
 */
class ExpiringOfflineOAuth
{
    /**
     * @param  array<string, mixed>  $cookies
     * @param  array<string, mixed>  $query
     */
    public static function callback(array $cookies, array $query, ?callable $setCookieFunction = null): AuthSession
    {
        Context::throwIfUninitialized();
        Context::throwIfPrivateApp('OAuth is not allowed for private apps');

        $cookieState = self::oauth('getStateCookie', $cookies);
        if (!self::oauth('isCallbackQueryValid', $query, $cookieState)) {
            throw new InvalidOAuthException('Invalid OAuth callback.');
        }

        $shop = Utils::sanitizeShopDomain($query['shop'] ?? '');
        $tokens = new ShopifyTokenService();
        $payload = $tokens->exchangeAuthorizationCode($shop, (string) $query['code']);

        $session = new AuthSession(OAuth::getOfflineSessionId($shop), $shop, false, '');
        $session->setAccessToken($payload['access_token']);
        $session->setScope($payload['scope'] ?? '');

        if (!Context::$SESSION_STORAGE->storeSession($session)) {
            throw new SessionStorageException(
                'OAuth Session could not be saved. Please check your session storage functionality.',
            );
        }

        $tokens->persistFromAuthorizationResponse($session->getId(), $payload);

        $expires = $session->getExpires() ? (int) $session->getExpires()->format('U') : null;
        $ok = self::oauth('setSessionIdCookie', $setCookieFunction, $session->getId(), Context::$IS_EMBEDDED_APP ? time() : $expires);
        $ok = $ok && self::oauth('setStateCookie', $setCookieFunction, $cookieState, time());
        if (!$ok) {
            throw new CookieSetException('OAuth Cookie could not be saved.');
        }

        return $session;
    }

    private static function oauth(string $method, ...$args)
    {
        $m = (new ReflectionClass(OAuth::class))->getMethod($method);
        $m->setAccessible(true);

        return $m->invoke(null, ...$args);
    }
}
