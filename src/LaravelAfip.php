<?php

namespace litvinjuan\LaravelAfip;

use litvinjuan\LaravelAfip\WebServices\AuthenticationWebService;
use litvinjuan\LaravelAfip\WebServices\ElectronicBillingWebService;
use litvinjuan\LaravelAfip\WebServices\PadronWebService;

class LaravelAfip
{
    public function auth(AfipConfiguration $configuration = null): AuthenticationWebService
    {
        return new AuthenticationWebService($configuration);
    }

    public function billing(AfipConfiguration $configuration = null): ElectronicBillingWebService
    {
        return new ElectronicBillingWebService($configuration);
    }

    public function padron(AfipConfiguration $configuration = null): PadronWebService
    {
        return new PadronWebService($configuration);
    }
}
