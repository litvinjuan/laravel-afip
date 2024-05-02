<?php

namespace litvinjuan\LaravelAfip\Clients;

use litvinjuan\LaravelAfip\AfipConfiguration;
use litvinjuan\LaravelAfip\Authorization\AfipTokenAuthorizationProvider;
use litvinjuan\LaravelAfip\Authorization\TokenAuthorization;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipAuthenticationException;
use litvinjuan\LaravelAfip\Exceptions\AfipException;
use litvinjuan\LaravelAfip\Exceptions\AfipSigningException;
use litvinjuan\LaravelAfip\Exceptions\AfipSoapException;

class AfipClient
{
    private ?TokenAuthorization $tokenAuthorization = null;

    private ?AfipSoapClient $soapClient = null;

    private AfipService $service;

    private AfipConfiguration $configuration;

    public function __construct(AfipService $service, AfipConfiguration $configuration)
    {
        $this->service = $service;
        $this->configuration = $configuration;
    }

    /**
     * @throws AfipSoapException
     */
    private function getClient(): AfipSoapClient
    {
        if (is_null($this->soapClient)) {
            $this->soapClient = new AfipSoapClient($this->service, $this->configuration);
        }

        return $this->soapClient;
    }

    /**
     * @throws AfipException|AfipSoapException
     */
    public function call(string $name, ?array $params = []): array
    {
        return $this->getClient()->call($name, $params);
    }

    /**
     * @throws AfipAuthenticationException|AfipSigningException
     */
    private function getTokenAuthorization(): ?TokenAuthorization
    {
        if (! $this->tokenAuthorization) {
            $this->tokenAuthorization = AfipTokenAuthorizationProvider::for($this->configuration, $this->service);
        }

        return $this->tokenAuthorization;
    }

    /**
     * @throws AfipAuthenticationException|AfipSigningException
     */
    public function getToken(): string
    {
        return optional($this->getTokenAuthorization())->getToken();
    }

    /**
     * @throws AfipAuthenticationException|AfipSigningException
     */
    public function getSign(): string
    {
        return optional($this->getTokenAuthorization())->getSign();
    }
}
