<?php

namespace litvinjuan\LaravelAfip;

use litvinjuan\LaravelAfip\Enum\AfipPadron;
use litvinjuan\LaravelAfip\WebServices\PadronWebService;

class LaravelAfip
{
    public static function padron(string $cuit, AfipPadron $padron): PadronWebService
    {
        return new PadronWebService($cuit, $padron);
    }
}
