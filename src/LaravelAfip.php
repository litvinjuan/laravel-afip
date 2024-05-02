<?php

namespace litvinjuan\LaravelAfip;

use litvinjuan\LaravelAfip\WebServices\ElectronicBillingWebService;
use litvinjuan\LaravelAfip\WebServices\PadronWebService;

class LaravelAfip
{
    public function billing(?AfipConfiguration $configuration = null): ElectronicBillingWebService
    {
        return new ElectronicBillingWebService($configuration);
    }

    public function padron(?AfipConfiguration $configuration = null): PadronWebService
    {
        return new PadronWebService($configuration);
    }
}
