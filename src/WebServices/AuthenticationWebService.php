<?php

namespace litvinjuan\LaravelAfip\WebServices;

use litvinjuan\LaravelAfip\AfipConfiguration;
use litvinjuan\LaravelAfip\Clients\AfipClient;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipAuthenticationException;

class AuthenticationWebService
{
    private AfipConfiguration $configuration;

    private AfipClient $client;

    public function __construct(?AfipConfiguration $configuration = null)
    {
        $this->configuration = $configuration ?? new AfipConfiguration();

        $this->client = new AfipClient(AfipService::wsaa, $this->configuration);
    }

    public function login(string $cms): array
    {
        try {
            return $this->client->call('loginCms', [
                'in0' => $cms,
            ]);
        } catch (\Exception $exception) {
            throw new AfipAuthenticationException($exception);
        }
    }
}
