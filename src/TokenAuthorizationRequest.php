<?php

namespace litvinjuan\LaravelAfip;

use Carbon\Carbon;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\WebServices\AuthenticationWebService;
use SimpleXMLElement;

class TokenAuthorizationRequest
{
    private AfipService $service;

    private AfipConfiguration $configuration;

    private AuthenticationWebService $authenticationWebService;

    public function __construct(AfipConfiguration $configuration, AfipService $service)
    {
        $this->configuration = $configuration;
        $this->service = $service;
        $this->authenticationWebService = new AuthenticationWebService($this->configuration);
    }

    public function createTokenAuthorization(): TokenAuthorization
    {
        $cms = $this->generateCms();
        $response = $this->authenticationWebService->login($cms);

        return new TokenAuthorization(
            $response['credentials']['token'],
            $response['credentials']['sign'],
            Carbon::make($response['header']['expirationTime'])
        );
    }

    private function generateCms(): string
    {
        return $this->signCms(
            $this->generateCmsBody()
        );
    }

    private function generateCmsBody(): string
    {
        $tra = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'.
            '<loginTicketRequest version="1.0">'.
            '</loginTicketRequest>');

        $tra->addChild('header');
        $tra->header->addChild('uniqueId', date('U'));
        $tra->header->addChild('generationTime', date('c', date('U') - 60));
        $tra->header->addChild('expirationTime', date('c', date('U') + 60));
        $tra->addChild('service', $this->translateServiceName());

        return $tra->asXML();
    }

    private function signCms(string $cmsBody)
    {
        $signer = $this->configuration->getSigner();

        return $signer->sign($cmsBody);
    }

    private function translateServiceName(): string
    {
        return match ($this->service) {
            AfipService::wsaa => 'wsaa',
            AfipService::wsfe => 'wsfe',
            AfipService::padron4 => 'ws_sr_padron_a4',
            AfipService::padron5 => 'ws_sr_padron_a5',
            AfipService::padron10 => 'ws_sr_padron_a10',
            AfipService::padron13 => 'ws_sr_padron_a13',
        };
    }
}
