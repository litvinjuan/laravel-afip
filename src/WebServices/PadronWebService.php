<?php

namespace litvinjuan\LaravelAfip\WebServices;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use litvinjuan\LaravelAfip\Enum\AfipPadron;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipException;
use litvinjuan\LaravelAfip\Exceptions\AfipSoapException;
use litvinjuan\LaravelAfip\Transformers\PersonaV1Transformer;
use litvinjuan\LaravelAfip\Transformers\PersonaV2Transformer;
use litvinjuan\LaravelAfip\Transformers\Transformer;

class PadronWebService extends WebService
{
    private AfipPadron $afipPadron;

    public function __construct(string $cuit, AfipPadron $afipPadron)
    {
        parent::__construct($cuit);

        $this->afipPadron = $afipPadron;
    }

    protected function getAfipService(): AfipService
    {
        return match ($this->afipPadron) {
            AfipPadron::Padron4 => AfipService::padron4,
            AfipPadron::Padron5 => AfipService::padron5,
            AfipPadron::Padron10 => AfipService::padron10,
            AfipPadron::Padron13 => AfipService::padron13,
        };
    }

    public function getMethodName(): string
    {
        return match ($this->afipPadron) {
            AfipPadron::Padron4, AfipPadron::Padron10, AfipPadron::Padron13 => 'getPersona',
            AfipPadron::Padron5 => 'getPersona_V2',
        };
    }

    /**
     * @throws AfipException
     * @throws AfipSoapException
     */
    private function call(string $name, ?array $params = []): array
    {
        $paramsWithAuth = array_merge($params, $this->getAuthData());

        $response = $this->request($name, $paramsWithAuth);

        $resultKey = "{$name}Result";
        $result = Arr::get($response, $resultKey);

        if (Arr::has($result, 'Errors')) {
            $this->throwFirstError($result);
        }

        return $result;
    }

    public function getPerson(string $cuit): ?array
    {
        try {
            $response = $this->call($this->getMethodName(), [
                'idPersona' => $cuit,
            ]);
        } catch (AfipSoapException $exception) {
            if (Str::contains($exception->getMessage(), 'No existe persona con ese Id')) {
                return null;
            }
            throw $exception;
        }

        return $this->getTransformer()->transform($response['personaReturn']);
    }

    public function status(): bool
    {
        $result = $this->call('dummy');

        return collect($result['return'])
            ->every(function ($value, $key) {
                return $value === 'OK';
            });
    }

    protected function getTransformer(): ?Transformer
    {
        return match ($this->afipPadron) {
            AfipPadron::Padron4, AfipPadron::Padron10, AfipPadron::Padron13 => new PersonaV1Transformer(),
            AfipPadron::Padron5 => new PersonaV2Transformer(),
        };
    }

    protected function getSoapVersioin(): int
    {
        return SOAP_1_1;
    }

    private function getAuthData(): array
    {
        return [
            'token' => $this->getTokenAuthorization()->getToken(),
            'sign' => $this->getTokenAuthorization()->getSign(),
            'cuitRepresentada' => $this->cuit,
        ];
    }

    /**
     * @throws AfipException
     */
    private function throwFirstError(array $result): void
    {
        $error = $result['Errors']['Err'];
        throw new AfipException($error['Msg'], $error['Code']);
    }
}
