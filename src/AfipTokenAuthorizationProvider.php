<?php

namespace litvinjuan\LaravelAfip;

use Illuminate\Support\Facades\Cache;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipInvalidServiceException;

class AfipTokenAuthorizationProvider
{
    public static function for(AfipConfiguration $configuration, AfipService $service): TokenAuthorization
    {
        if ($service === AfipService::wsaa) {
            throw new AfipInvalidServiceException("Cannot generate a token authorization for the wsaa service. It's an authentication service and is used to generate other token authorizations.");
        }

        $key = self::getCacheKey($configuration, $service);

        if (Cache::missing($key)) {
            $tokenAuthorization = self::createServiceTokenAuthorization($configuration, $service);
            Cache::put($key, $tokenAuthorization, $tokenAuthorization->getExpiresAt());
        }

        return Cache::get($key);
    }

    public static function createServiceTokenAuthorization(AfipConfiguration $configuration, AfipService $service): TokenAuthorization
    {
        $tokenAuthorizationRequest = new TokenAuthorizationRequest(
            $configuration,
            $service
        );

        return $tokenAuthorizationRequest->createTokenAuthorization();
    }

    private static function getCacheKey(AfipConfiguration $configuration, AfipService $service): string
    {
        if ($configuration->isProduction()) {
            return "AFIP-TA-{$service->name}";
        }

        return "AFIP-TA-{$service->name}-dev";
    }
}
