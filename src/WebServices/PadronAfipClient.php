<?php

namespace litvinjuan\LaravelAfip\WebServices;

use Illuminate\Support\Str;
use litvinjuan\LaravelAfip\Enum\AfipPadron;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipSoapException;
use litvinjuan\LaravelAfip\Transformers\PersonaV1Transformer;
use litvinjuan\LaravelAfip\Transformers\PersonaV2Transformer;
use litvinjuan\LaravelAfip\Transformers\Transformer;

class PadronAfipClient
{
    private string $cuit;

    private AfipClient $padron4Client;

    private AfipClient $padron5Client;

    private AfipClient $padron10Client;

    private AfipClient $padron13Client;

    public function __construct(string $cuit)
    {
        $this->cuit = $cuit;

        $this->padron4Client = new AfipClient($cuit, AfipService::padron4);
        $this->padron5Client = new AfipClient($cuit, AfipService::padron5);
        $this->padron10Client = new AfipClient($cuit, AfipService::padron10);
        $this->padron13Client = new AfipClient($cuit, AfipService::padron13);
    }

    private function getClient(AfipPadron $afipPadron): AfipClient
    {
        return match ($afipPadron) {
            AfipPadron::Padron4 => $this->padron4Client,
            AfipPadron::Padron5 => $this->padron5Client,
            AfipPadron::Padron10 => $this->padron10Client,
            AfipPadron::Padron13 => $this->padron13Client,
        };
    }

    public function getMethodName(AfipPadron $afipPadron): string
    {
        return match ($afipPadron) {
            AfipPadron::Padron4, AfipPadron::Padron10, AfipPadron::Padron13 => 'getPersona',
            AfipPadron::Padron5 => 'getPersona_V2',
        };
    }

    public function getPerson(string $cuit, AfipPadron $padron): ?array
    {
        try {
            $response = $this->getClient($padron)->call(
                $this->getMethodName($padron),
                [
                    'token' => $this->getClient($padron)->getToken(),
                    'sign' => $this->getClient($padron)->getSign(),
                    'cuitRepresentada' => $this->cuit,
                    'idPersona' => $cuit,
                ]
            );

            return $this->getTransformer($padron)->transform($response);
        } catch (AfipSoapException $exception) {
            if (Str::contains($exception->getMessage(), 'No existe persona con ese Id')) {
                return null;
            }
            throw $exception;
        }
    }

    public function status(): bool
    {
        $result = $this->getClient(AfipPadron::Padron4)->call('dummy');

        return collect($result['return'])
            ->every(function ($value, $key) {
                return $value === 'OK';
            });
    }

    private function getTransformer(AfipPadron $padron): ?Transformer
    {
        return match ($padron) {
            AfipPadron::Padron4, AfipPadron::Padron10, AfipPadron::Padron13 => new PersonaV1Transformer(),
            AfipPadron::Padron5 => new PersonaV2Transformer(),
        };
    }
}
