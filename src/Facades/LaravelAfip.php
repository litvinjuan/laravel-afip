<?php

namespace litvinjuan\LaravelAfip\Facades;

use Illuminate\Support\Facades\Facade;
use litvinjuan\LaravelAfip\WebServices\ElectronicBillingAfipClient;
use litvinjuan\LaravelAfip\WebServices\PadronAfipClient;

/**
 * @method static ElectronicBillingAfipClient electronicBilling(string $cuit)
 * @method static PadronAfipClient padron(string $cuit)
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
