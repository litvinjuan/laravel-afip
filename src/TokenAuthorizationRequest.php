<?php

namespace litvinjuan\LaravelAfip;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipAuthenticationException;
use SimpleXMLElement;
use SoapClient;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class TokenAuthorizationRequest
{
    private string $cuit;

    private AfipService $afipService;

    private bool $production;

    private TemporaryDirectory $temporaryDirectory;

    private string $cms;

    private TokenAuthorization $tokenAuthorization;

    public function __construct(string $cuit, AfipService $afipService, bool $production, string $key)
    {
        $this->cuit = $cuit;
        $this->afipService = $afipService;
        $this->production = $production;

        $this->temporaryDirectory = (new TemporaryDirectory())->name($this->getKey())->force()->create();

        $this->generateXml();
        $this->sign();
    }

    private function getKey(): string
    {
        if ($this->production) {
            return "TA-{$this->cuit}-{$this->afipService->name}";
        }

        return "TA-{$this->cuit}-{$this->afipService->name}-dev";
    }

    private function getXmlPath(): string
    {
        return $this->temporaryDirectory->path("{$this->getKey()}.xml");
    }

    private function getTmpPath(): string
    {
        return $this->temporaryDirectory->path("{$this->getKey()}.tmp");
    }

    private function generateXml(): void
    {
        $tra = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'.
            '<loginTicketRequest version="1.0">'.
            '</loginTicketRequest>');

        $tra->addChild('header');
        $tra->header->addChild('uniqueId', date('U'));
        $tra->header->addChild('generationTime', date('c', date('U') - 60));
        $tra->header->addChild('expirationTime', date('c', date('U') + 60));
        $tra->addChild('service', $this->afipService->name);
        $xml = $tra->asXML();

        File::put($this->getXmlPath(), $xml);
    }

    private function sign(): void
    {
        $xmlPath = $this->getXmlPath();
        $tmpPath = $this->getTmpPath();

        try {
            $this->cms = AfipSigning::sign($xmlPath, $tmpPath);
        } finally {
            // Delete temporary files regardless of success or failure
            $this->temporaryDirectory->delete();
        }
    }

    public function createTokenAuthorization(): TokenAuthorization
    {
        $client = new SoapClient(
            self::getWsdl(AfipService::wsaa),
            [
                'soap_version' => SOAP_1_2,
                'location' => $this->getWsaaUrl(),
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
            $loginResult = $client->__soapCall('loginCms', [
                'in0' => $this->cms,
            ]);
        } catch (\Exception $exception) {
            throw new AfipAuthenticationException($exception);
        }

        $response = new SimpleXMLElement($loginResult->loginCmsReturn);

        $this->tokenAuthorization = new TokenAuthorization(
            $response->credentials->token,
            $response->credentials->sign,
            Carbon::make($response->header->expirationTime)
        );

        return $this->tokenAuthorization;
    }

    private function getWsdl(AfipService $afipService): string
    {
        return __DIR__.'/wsdl/'.$afipService->name.'.wsdl';
    }

    private function getWsaaUrl(): string
    {
        if ($this->production) {
            return 'https://wsaa.afip.gov.ar/ws/services/LoginCms';
        } else {
            return 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms';
        }
    }
}
