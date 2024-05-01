<?php

namespace litvinjuan\LaravelAfip;

use litvinjuan\LaravelAfip\Enum\AfipService;

class AfipWsdl
{
    public static function for(AfipService $service, AfipConfiguration $configuration)
    {
        $filename = self::getWdslFilename($service, $configuration);

        return __DIR__.'/wsdl/'.$filename;
    }

    private static function getWdslFilename(AfipService $service, AfipConfiguration $configuration)
    {
        if ($configuration->isProduction()) {
            return match ($service) {
                AfipService::wsaa => 'wsaa-production.wsdl',
                AfipService::wsfe => 'wsfe-production.wsdl',
                AfipService::padron4 => 'ws_sr_padron_a4-production.wsdl',
                AfipService::padron5 => 'ws_sr_padron_a5-production.wsdl',
                AfipService::padron10 => 'ws_sr_padron_a10-production.wsdl',
                AfipService::padron13 => 'ws_sr_padron_a13-production.wsdl',
            };
        }

        return match ($service) {
            AfipService::wsaa => 'wsaa.wsdl',
            AfipService::wsfe => 'wsfe.wsdl',
            AfipService::padron4 => 'ws_sr_padron_a4.wsdl',
            AfipService::padron5 => 'ws_sr_padron_a5.wsdl',
            AfipService::padron10 => 'ws_sr_padron_a10.wsdl',
            AfipService::padron13 => 'ws_sr_padron_a13.wsdl',
        };
    }
}
