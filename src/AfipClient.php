<?php

namespace litvinjuan\LaravelAfip;

use litvinjuan\LaravelAfip\Enum\AfipService;
use SoapClient;

class AfipClient
{
    //    private string $cuit;
    //    private AfipService $afipService;
    //    private bool $production;
    //    private SoapClient $client;
    //    private TokenAuthorization $tokenAuthorization;
    //
    //    public function __construct(string $cuit, AfipService $afipService, bool $production)
    //    {
    //        $this->cuit = $cuit;
    //        $this->afipService = $afipService;
    //        $this->production = $production;
    //
    //        $this->client = new SoapClient(
    //            $this->getWdsl(),
    //            [
    //                'soap_version'   => SOAP_1_2,
    //                'location'       => $this->getUrl(),
    //                'trace'          => 1,
    //                'stream_context' => stream_context_create([
    //                    'ssl' => [
    //                        'ciphers' => 'AES256-SHA',
    //                        'verify_peer' => false,
    //                        'verify_peer_name' => false,
    //                    ],
    //                ]),
    //            ]
    //        );
    //    }
    //
    //    private function getTokenAuthorization()
    //    {
    //        if (! $this->tokenAuthorization) {
    //            $this->tokenAuthorization = AfipAuthentication::getTokenAuthorizationForService(
    //                $this->cuit,
    //                $this->afipService,
    //                $this->production
    //            );
    //        }
    //
    //        return $this->tokenAuthorization;
    //    }
    //
    //    public function call(string $name, array $params)
    //    {
    //        return $this->client->__soapCall($name, $params);
    //    }
    //
    //    private function getWdsl()
    //    {
    //        return __DIR__ . '/wsdl/' . $this->getWdslFilename();
    //    }
    //
    //    private function getWdslFilename()
    //    {
    //        if ($this->production) {
    //            return match ($this->afipService) {
    //                AfipService::wsaa => 'wsaa-production.wsdl',
    //                AfipService::wsfe => 'wsfe-production.wsdl',
    //                AfipService::padron4 => 'ws_sr_padron_a4-production.wsdl',
    //                AfipService::padron5 => 'ws_sr_padron_a5-production.wsdl',
    //                AfipService::padron10 => 'ws_sr_padron_a10-production.wsdl',
    //                AfipService::padron13 => 'ws_sr_padron_a13-production.wsdl',
    //            };
    //        }
    //
    //        return match ($this->afipService) {
    //            AfipService::wsaa => 'wsaa.wsdl',
    //            AfipService::wsfe => 'wsfe.wsdl',
    //            AfipService::padron4 => 'ws_sr_padron_a4.wsdl',
    //            AfipService::padron5 => 'ws_sr_padron_a5.wsdl',
    //            AfipService::padron10 => 'ws_sr_padron_a10.wsdl',
    //            AfipService::padron13 => 'ws_sr_padron_a13.wsdl',
    //        };
    //    }
    //
    //    private function getUrl()
    //    {
    //        if ($this->production) {
    //            return match ($this->afipService) {
    //                AfipService::wsaa => 'https://wsaa.afip.gov.ar/ws/services/LoginCms',
    //                AfipService::wsfe => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx',
    //                AfipService::padron4 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA4',
    //                AfipService::padron5 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5',
    //                AfipService::padron10 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA10',
    //                AfipService::padron13 => 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA13',
    //            };
    //        }
    //
    //        return match ($this->afipService) {
    //            AfipService::wsaa => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms',
    //            AfipService::wsfe => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx',
    //            AfipService::padron4 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA4',
    //            AfipService::padron5 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA5',
    //            AfipService::padron10 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA10',
    //            AfipService::padron13 => 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA13',
    //        };
    //    }
}
