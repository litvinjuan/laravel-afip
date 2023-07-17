<?php

namespace litvinjuan\LaravelAfip\WebServices;

use Illuminate\Support\Arr;
use litvinjuan\LaravelAfip\AfipAuthentication;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipException;
use litvinjuan\LaravelAfip\Exceptions\AfipSoapException;
use litvinjuan\LaravelAfip\TokenAuthorization;
use SoapClient;

class AfipClient
{
    private string $cuit;

    private ?TokenAuthorization $tokenAuthorization = null;

    private SoapClient $soapClient;
    private AfipService $afipService;

    public function __construct(string $cuit, AfipService $afipService)
    {
        $this->cuit = $cuit;
        $this->afipService = $afipService;

        $this->soapClient = new SoapClient(
            $this->getWdsl(),
            [
                'soap_version' => $this->getSoapVersion(),
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
    }

    public function call(string $name, ?array $params = [])
    {
        try {
            $rawResponse = $this->soapClient->{$name}($params);

            $response = Arr::get(json_decode(json_encode($rawResponse), true), $this->getReturnKey($name));

            if (Arr::has($response, 'Errors')) {
                $this->throwFirstError($response);
            }

            return $response;
        } catch (\SoapFault $exception) {
            throw new AfipSoapException($exception);
        }
    }

    /**
     * @throws AfipException
     */
    private function throwFirstError(array $result): void
    {
        $error = $result['Errors']['Err'];
        throw new AfipException($error['Msg'], $error['Code']);
    }

    private function getTokenAuthorization()
    {
        if (! $this->tokenAuthorization) {
            $this->tokenAuthorization = AfipAuthentication::getTokenAuthorizationForService(
                $this->cuit,
                $this->afipService,
                $this->isProduction()
            );
        }

        return $this->tokenAuthorization;
    }

    public function getToken(): string
    {
        return $this->getTokenAuthorization()->getToken();
    }

    public function getSign(): string
    {
        return $this->getTokenAuthorization()->getSign();
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
            return match ($this->afipService) {
                AfipService::wsaa => 'wsaa-production.wsdl',
                AfipService::wsfe => 'wsfe-production.wsdl',
                AfipService::padron4 => 'ws_sr_padron_a4-production.wsdl',
                AfipService::padron5 => 'ws_sr_padron_a5-production.wsdl',
                AfipService::padron10 => 'ws_sr_padron_a10-production.wsdl',
                AfipService::padron13 => 'ws_sr_padron_a13-production.wsdl',
            };
        }

        return match ($this->afipService) {
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
            return match ($this->afipService) {
                AfipService::wsaa => 'https://wsaa.afip.gov.ar/ws/services/LoginCms',
                AfipService::wsfe => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx',
                AfipService::padron4 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA4',
                AfipService::padron5 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5',
                AfipService::padron10 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA10',
                AfipService::padron13 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA13',
            };
        }

        return match ($this->afipService) {
            AfipService::wsaa => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms',
            AfipService::wsfe => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx',
            AfipService::padron4 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA4',
            AfipService::padron5 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA5',
            AfipService::padron10 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA10',
            AfipService::padron13 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA13',
        };
    }

    private function getSoapVersion()
    {
        return match ($this->afipService) {
            AfipService::wsaa, AfipService::wsfe => SOAP_1_2,
            AfipService::padron4, AfipService::padron13, AfipService::padron10, AfipService::padron5 => SOAP_1_1,
        };
    }

    private function getReturnKey(string $name): string
    {
        return match ($this->afipService) {
            AfipService::wsaa => "{$name}Return",
            AfipService::wsfe => "{$name}Result",
            AfipService::padron4, AfipService::padron5, AfipService::padron10, AfipService::padron13 => "personaReturn",
        };
    }
}
