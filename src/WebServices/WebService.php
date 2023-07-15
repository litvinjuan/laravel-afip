<?php

namespace litvinjuan\LaravelAfip\WebServices;

use litvinjuan\LaravelAfip\AfipAuthentication;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipException;
use litvinjuan\LaravelAfip\TokenAuthorization;
use litvinjuan\LaravelAfip\Transformers\Transformer;
use SoapClient;

abstract class WebService
{
    protected string $cuit;

    private ?TokenAuthorization $tokenAuthorization = null;

    public function __construct(string $cuit)
    {
        $this->cuit = $cuit;
    }

    protected function call(string $name, array $params)
    {
        $client = new SoapClient(
            $this->getWdsl(),
            [
                'soap_version' => $this->getSoapVersioin(),
                'location' => $this->getUrl(),
                'trace' => 1,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'ciphers' => 'AES256-SHA',
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]),
            ]
        );

        try {
            $response = $client->{$name}($params);
            $jsonResponse = json_decode(json_encode($response), true)[$this->getReturnKey()];
            return $this->getTransformer()->transform($jsonResponse);
        } catch (\SoapFault $exception) {
            throw new AfipException($exception);
        }
    }

    abstract protected function getAfipService(): AfipService;

    abstract protected function getReturnKey(): string;

    abstract protected function getSoapVersioin(): int;

    protected function getTransformer(): ?Transformer
    {
        return null;
    }

    protected function getTokenAuthorization()
    {
        if (! $this->tokenAuthorization) {
            $this->tokenAuthorization = AfipAuthentication::getTokenAuthorizationForService(
                $this->cuit,
                $this->getAfipService(),
                $this->isProduction()
            );
        }

        return $this->tokenAuthorization;
    }

    public function isProduction()
    {
        return config('afip.production');
    }

    private function getWdsl()
    {
        return __DIR__.'/../wsdl/'.$this->getWdslFilename();
    }

    private function getWdslFilename()
    {
        if ($this->isProduction()) {
            return match ($this->getAfipService()) {
                AfipService::wsaa => 'wsaa-production.wsdl',
                AfipService::wsfe => 'wsfe-production.wsdl',
                AfipService::padron4 => 'ws_sr_padron_a4-production.wsdl',
                AfipService::padron5 => 'ws_sr_padron_a5-production.wsdl',
                AfipService::padron10 => 'ws_sr_padron_a10-production.wsdl',
                AfipService::padron13 => 'ws_sr_padron_a13-production.wsdl',
            };
        }

        return match ($this->getAfipService()) {
            AfipService::wsaa => 'wsaa.wsdl',
            AfipService::wsfe => 'wsfe.wsdl',
            AfipService::padron4 => 'ws_sr_padron_a4.wsdl',
            AfipService::padron5 => 'ws_sr_padron_a5.wsdl',
            AfipService::padron10 => 'ws_sr_padron_a10.wsdl',
            AfipService::padron13 => 'ws_sr_padron_a13.wsdl',
        };
    }

    private function getUrl()
    {
        if ($this->isProduction()) {
            return match ($this->getAfipService()) {
                AfipService::wsaa => 'https://wsaa.afip.gov.ar/ws/services/LoginCms',
                AfipService::wsfe => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx',
                AfipService::padron4 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA4',
                AfipService::padron5 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5',
                AfipService::padron10 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA10',
                AfipService::padron13 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA13',
            };
        }

        return match ($this->getAfipService()) {
            AfipService::wsaa => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms',
            AfipService::wsfe => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx',
            AfipService::padron4 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA4',
            AfipService::padron5 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA5',
            AfipService::padron10 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA10',
            AfipService::padron13 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA13',
        };
    }
}
