<?php

namespace litvinjuan\LaravelAfip\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \litvinjuan\LaravelAfip\LaravelAfip
 */
class LaravelAfip extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \litvinjuan\LaravelAfip\LaravelAfip::class;
    }
}
