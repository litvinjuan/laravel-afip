<?php

namespace litvinjuan\LaravelAfip\Clients;

use Exception;
use Illuminate\Support\Arr;
use litvinjuan\LaravelAfip\AfipConfiguration;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipException;
use litvinjuan\LaravelAfip\Exceptions\AfipSoapException;
use SimpleXMLElement;
use SoapClient;
use SoapFault;

class AfipSoapClient
{
    private ?SoapClient $soapClient = null;

    private AfipService $service;

    private AfipConfiguration $configuration;

    /**
     * @throws AfipSoapException
     */
    public function __construct(AfipService $service, AfipConfiguration $configuration)
    {
        $this->service = $service;
        $this->configuration = $configuration;

        try {
            $this->soapClient = new SoapClient(
                $this->service->getwsdl($this->configuration),
                [
                    'soap_version' => $this->getSoapVersion(),
                    'location' => $this->service->getUrl($this->configuration),
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
        } catch (SoapFault $exception) {
            throw new AfipSoapException($exception);
        }
    }

    /**
     * @throws AfipException|AfipSoapException
     */
    public function call(string $name, ?array $params = []): array
    {
        try {
            $rawResponse = $this->soapClient->{$name}($params);

            $response = $this->convertResponseToJson($rawResponse, $name);

            if (Arr::has($response, 'Errors')) {
                $this->throwFirstError($response);
            }

            return $response;
        } catch (SoapFault|Exception $exception) {
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

    /**
     * @throws \Exception
     */
    private function convertResponseToJson(mixed $response, string $methodName): array
    {
        $responseKey = $this->getReturnKey($methodName);

        if ($this->service === AfipService::wsaa) {
            return json_decode(json_encode(new SimpleXMLElement($response->{$responseKey})), true);
        }

        return Arr::get(json_decode(json_encode($response), true), $responseKey);
    }

    private function getSoapVersion(): int
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
