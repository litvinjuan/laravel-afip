<?php

namespace litvinjuan\LaravelAfip\WebServices;

use Illuminate\Support\Arr;
use litvinjuan\LaravelAfip\AfipConfiguration;
use litvinjuan\LaravelAfip\AfipTokenAuthorizationProvider;
use litvinjuan\LaravelAfip\AfipWebServiceUrl;
use litvinjuan\LaravelAfip\AfipWsdl;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipException;
use litvinjuan\LaravelAfip\Exceptions\AfipSoapException;
use litvinjuan\LaravelAfip\TokenAuthorization;
use SimpleXMLElement;
use SoapClient;

class AfipClient
{
    private ?TokenAuthorization $tokenAuthorization = null;

    private ?SoapClient $soapClient = null;

    private AfipService $service;

    private AfipConfiguration $configuration;

    public function __construct(AfipService $service, AfipConfiguration $configuration)
    {
        $this->service = $service;
        $this->configuration = $configuration;
    }

    private function getClient(): SoapClient
    {
        if (is_null($this->soapClient)) {
            $this->soapClient = new SoapClient(
                AfipWsdl::for($this->service, $this->configuration),
                [
                    'soap_version' => $this->getSoapVersion(),
                    'location' => AfipWebServiceUrl::for($this->service, $this->configuration),
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

        return $this->soapClient;
    }

    public function call(string $name, ?array $params = [])
    {
        try {
            $client = $this->getClient();
            $rawResponse = $client->{$name}($params);

            $response = $this->convertResponseToJson($rawResponse, $name);

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

    private function getTokenAuthorization(): TokenAuthorization
    {
        if (! $this->tokenAuthorization) {
            $this->tokenAuthorization = AfipTokenAuthorizationProvider::for($this->configuration, $this->service);
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

    private function convertResponseToJson(mixed $response, string $methodName): array
    {
        $responseKey = $this->getReturnKey($methodName);

        if ($this->service === AfipService::wsaa) {
            return json_decode(json_encode(new SimpleXMLElement($response->{$responseKey})), true);
        }

        return Arr::get(json_decode(json_encode($response), true), $responseKey);
    }

    private function getSoapVersion()
    {
        return match ($this->service) {
            AfipService::wsaa, AfipService::wsfe => SOAP_1_2,
            AfipService::padron4, AfipService::padron13, AfipService::padron10, AfipService::padron5 => SOAP_1_1,
        };
    }

    private function getReturnKey(string $name): string
    {
        return match ($this->service) {
            AfipService::wsaa => "{$name}Return",
            AfipService::wsfe => "{$name}Result",
            AfipService::padron4, AfipService::padron5, AfipService::padron10, AfipService::padron13 => 'personaReturn',
        };
    }
}
