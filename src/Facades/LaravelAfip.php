<?php

namespace litvinjuan\LaravelAfip\Facades;

use Illuminate\Support\Facades\Facade;
use litvinjuan\LaravelAfip\AfipConfiguration;
use litvinjuan\LaravelAfip\WebServices\ElectronicBillingWebService;
use litvinjuan\LaravelAfip\WebServices\PadronWebService;

/**
 * @method static ElectronicBillingWebService billing(AfipConfiguration $configuration = null)
 * @method static PadronWebService padron(AfipConfiguration $configuration = null)
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
