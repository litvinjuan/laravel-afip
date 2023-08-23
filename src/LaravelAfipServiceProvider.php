<?php

namespace litvinjuan\LaravelAfip;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelAfipServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-afip')
            ->hasConfigFile('afip');
    }
}
