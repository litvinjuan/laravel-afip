<?php

namespace litvinjuan\LaravelAfip\WebServices;

use litvinjuan\LaravelAfip\Enum\AfipPadron;
use litvinjuan\LaravelAfip\Enum\AfipService;
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

    public function getPerson(string $cuit): mixed
    {
        return $this->call($this->getMethodName(), [
            'token' => $this->getTokenAuthorization()->getToken(),
            'sign' => $this->getTokenAuthorization()->getSign(),
            'cuitRepresentada' => $this->cuit,
            'idPersona' => $cuit,
        ]);
    }

    protected function getTransformer(): ?Transformer
    {
        return match ($this->afipPadron) {
            AfipPadron::Padron4, AfipPadron::Padron10, AfipPadron::Padron13 => new PersonaV1Transformer(),
            AfipPadron::Padron5 => new PersonaV2Transformer(),
        };
    }

    protected function getReturnKey(): string
    {
        return 'personaReturn';
    }

    protected function getSoapVersioin(): int
    {
        return SOAP_1_1;
    }
}
