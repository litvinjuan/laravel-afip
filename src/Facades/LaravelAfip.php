<?php

namespace litvinjuan\LaravelAfip\Facades;

use Illuminate\Support\Facades\Facade;
use litvinjuan\LaravelAfip\Enum\AfipPadron;
use litvinjuan\LaravelAfip\WebServices\ElectronicBillingWebService;
use litvinjuan\LaravelAfip\WebServices\PadronWebService;

/**
 * @method static ElectronicBillingWebService electronicBilling(string $cuit)
 * @method static PadronWebService padron(string $cuit, AfipPadron $padron)
 *
 * @see \litvinjuan\LaravelAfip\LaravelAfip
 */
class LaravelAfip extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \litvinjuan\LaravelAfip\LaravelAfip::class;
    }
}
