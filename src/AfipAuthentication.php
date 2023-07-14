<?php

namespace litvinjuan\LaravelAfip;

use Illuminate\Support\Facades\Cache;
use litvinjuan\LaravelAfip\Enum\AfipService;

class AfipAuthentication
{
    public static function getTokenAuthorizationForService(string $cuit, AfipService $afipService, bool $production)
    {
        $key = self::getKey($cuit, $afipService, $production);

        if (!Cache::has($key)) {
            $tokenAuthorization = self::createServiceTokenAuthorization($cuit, $afipService, $production, $key);
            Cache::put($key, $tokenAuthorization, $tokenAuthorization->getExpiresAt());
        }

        return Cache::get($key);
    }

    public static function createServiceTokenAuthorization(string $cuit, AfipService $afipService, bool $production, string $key): TokenAuthorization
    {
        $tokenAuthorizationRequest = new TokenAuthorizationRequest($cuit, $afipService, $production, $key);

        return $tokenAuthorizationRequest->createTokenAuthorization();
    }

    private static function getKey(string $cuit, AfipService $afipService, bool $production): string
    {
        if ($production) {
            return "TA-{$cuit}-{$afipService->name}";
        }

        return "TA-{$cuit}-{$afipService->name}-dev";
    }
}
