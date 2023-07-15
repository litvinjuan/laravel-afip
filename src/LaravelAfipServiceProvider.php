<?php

namespace litvinjuan\LaravelAfip;

use litvinjuan\LaravelAfip\Commands\InstallLaravelAfipCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelAfipServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-afip')
            ->hasConfigFile()
            ->hasCommand(InstallLaravelAfipCommand::class);
    }
}
