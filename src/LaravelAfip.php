<?php

namespace litvinjuan\LaravelAfip;

use litvinjuan\LaravelAfip\WebServices\ElectronicBillingAfipClient;
use litvinjuan\LaravelAfip\WebServices\PadronAfipClient;

class LaravelAfip
{
    public function electronicBilling(string $cuit): ElectronicBillingAfipClient
    {
        return new ElectronicBillingAfipClient($cuit);
    }

    public function padron(string $cuit): PadronAfipClient
    {
        return new PadronAfipClient($cuit);
    }
}
