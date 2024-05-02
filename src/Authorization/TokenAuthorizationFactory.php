<?php

namespace litvinjuan\LaravelAfip\Authorization;

use Carbon\Carbon;
use litvinjuan\LaravelAfip\AfipConfiguration;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipAuthenticationException;
use litvinjuan\LaravelAfip\Exceptions\AfipSigningException;
use litvinjuan\LaravelAfip\WebServices\AuthenticationWebService;
use SimpleXMLElement;

class TokenAuthorizationFactory
{
    private ?AfipService $service;

    private ?AfipConfiguration $configuration;

    private ?AuthenticationWebService $authenticationWebService = null;

    private function __construct(AfipService $service)
    {
        $this->service = $service;
    }

    public static function for(AfipService $service): self
    {
        return new self($service);
    }

    public function with(AfipConfiguration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @throws AfipAuthenticationException|AfipSigningException
     */
    public function create(): TokenAuthorization
    {
        $signedCms = $this->generateSignedCms();

        $response = $this->getAuthenticationWebService()->login($signedCms);

        return new TokenAuthorization(
            $response['credentials']['token'],
            $response['credentials']['sign'],
            Carbon::make($response['header']['expirationTime'])
        );
    }

    private function getAuthenticationWebService(): AuthenticationWebService
    {
        if (is_null($this->authenticationWebService)) {
            $this->authenticationWebService = new AuthenticationWebService($this->configuration);
        }

        return $this->authenticationWebService;
    }

    /**
     * @throws AfipSigningException
     */
    private function generateSignedCms(): string
    {
        return $this->signCms(
            $this->generateCmsBody()
        );
    }

    private function generateCmsBody(): string
    {
        $serviceName = $this->service->getServiceName();

        $tra = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'.
            '<loginTicketRequest version="1.0">'.
            '</loginTicketRequest>');

        $tra->addChild('header');
        $tra->header->addChild('uniqueId', date('U'));
        $tra->header->addChild('generationTime', date('c', date('U') - 60));
        $tra->header->addChild('expirationTime', date('c', date('U') + 60));
        $tra->addChild('service', $serviceName);

        return $tra->asXML();
    }

    /**
     * @throws AfipSigningException
     */
    private function signCms(string $cmsBody): string
    {
        $signer = $this->configuration->getSigner();

        return $signer->sign($cmsBody);
    }
}
