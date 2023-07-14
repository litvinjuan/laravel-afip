<?php

namespace litvinjuan\LaravelAfip;

use litvinjuan\LaravelAfip\Enum\AfipPadron;
use litvinjuan\LaravelAfip\WebServices\PadronWebService;

class LaravelAfip
{
    private string $cuit;

    private bool $production;

    public function init(string $cuit, bool $production = true)
    {
        $this->cuit = $cuit;
        $this->production = $production;
    }

    public function padron(AfipPadron $afipPadron): PadronWebService
    {
        return new PadronWebService($this->cuit, $afipPadron, $this->production);
    }
}
