<?php

namespace litvinjuan\LaravelAfip;

use litvinjuan\LaravelAfip\Enum\AfipService;

class AfipWebServiceUrl
{
    public static function for(AfipService $service, AfipConfiguration $configuration)
    {
        if ($configuration->isProduction()) {
            return match ($service) {
                AfipService::wsaa => 'https://wsaa.afip.gov.ar/ws/services/LoginCms',
                AfipService::wsfe => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx',
                AfipService::padron4 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA4',
                AfipService::padron5 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5',
                AfipService::padron10 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA10',
                AfipService::padron13 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA13',
            };
        }

        return match ($service) {
            AfipService::wsaa => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms',
            AfipService::wsfe => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx',
            AfipService::padron4 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA4',
            AfipService::padron5 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA5',
            AfipService::padron10 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA10',
            AfipService::padron13 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA13',
        };
    }
}
