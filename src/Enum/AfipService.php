<?php

namespace litvinjuan\LaravelAfip\Enum;

use litvinjuan\LaravelAfip\AfipConfiguration;

enum AfipService: string
{
    case wsaa = 'wsaa';
    case wsfe = 'wsfe';
    case padron4 = 'ws_sr_padron_a4';
    case padron5 = 'ws_sr_padron_constancia_inscripcion';
    case padron10 = 'ws_sr_padron_a10';
    case padron13 = 'ws_sr_padron_a13';

    public function getUrl(AfipConfiguration $configuration): string
    {
        if ($configuration->isProduction()) {
            return match ($this) {
                self::wsaa => 'https://wsaa.afip.gov.ar/ws/services/LoginCms',
                self::wsfe => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx',
                self::padron4 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA4',
                self::padron5 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5',
                self::padron10 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA10',
                self::padron13 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA13',
            };
        }

        return match ($this) {
            self::wsaa => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms',
            self::wsfe => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx',
            self::padron4 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA4',
            self::padron5 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA5',
            self::padron10 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA10',
            self::padron13 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA13',
        };
    }

    public function getWsdl(AfipConfiguration $configuration): string
    {
        $baseWsdlDirectory = __DIR__ . '/../wsdl';

        $filename = $this->value;

        if ($configuration->isProduction()) {
            $filename .= '-production';
        }

        return "{$baseWsdlDirectory}/{$filename}.wsdl";
    }

    public function getServiceName(): string
    {
        if ($this === self::padron5) {
            return 'ws_sr_padron_a5';
        }

        return $this->value;
    }
}
