<?php

namespace litvinjuan\LaravelAfip\WebServices;

use litvinjuan\LaravelAfip\Enum\AfipPadron;
use litvinjuan\LaravelAfip\Enum\AfipService;

class PadronWebService extends WebService
{
    private AfipPadron $afipPadron;

    public function __construct(string $cuit, AfipPadron $afipPadron, bool $production = true)
    {
        $this->afipPadron = $afipPadron;

        parent::__construct($cuit, $production);
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

    public function getPerson(string $cuit)
    {
        $this->call('getPersona', [
            'token' => $this->getTokenAuthorization()->getToken(),
            'sign' => $this->getTokenAuthorization()->getSign(),
            'cuitRepresentada' => $this->cuit,
            'idPersona'	=> $cuit,
        ]);
    }
}
