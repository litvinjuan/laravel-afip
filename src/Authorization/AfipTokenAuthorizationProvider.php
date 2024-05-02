<?php

namespace litvinjuan\LaravelAfip\Authorization;

use Illuminate\Support\Facades\Cache;
use litvinjuan\LaravelAfip\AfipConfiguration;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipAuthenticationException;
use litvinjuan\LaravelAfip\Exceptions\AfipSigningException;

class AfipTokenAuthorizationProvider
{
    /**
     * @throws AfipAuthenticationException|AfipSigningException
     */
    public static function for(AfipConfiguration $configuration, AfipService $service): ?TokenAuthorization
    {
        if ($service === AfipService::wsaa) {
            return null;
        }

        $key = self::getCacheKey($configuration, $service);

        if (Cache::missing($key)) {
            $tokenAuthorization = self::createServiceTokenAuthorization($configuration, $service);
            Cache::put($key, $tokenAuthorization, $tokenAuthorization->getExpiresAt());
        }

        return Cache::get($key);
    }

    /**
     * @throws AfipAuthenticationException|AfipSigningException
     */
    private static function createServiceTokenAuthorization(AfipConfiguration $configuration, AfipService $service): TokenAuthorization
    {
        return TokenAuthorizationFactory::for($service)
            ->with($configuration)
            ->create();
    }

    private static function getCacheKey(AfipConfiguration $configuration, AfipService $service): string
    {
        if ($configuration->isProduction()) {
            return "AFIP-TA-{$configuration->getPublicIdentifier()}-{$service->name}";
        }

        return "AFIP-TA-{$configuration->getPublicIdentifier()}-{$service->name}-dev";
    }
}
