<?php

namespace litvinjuan\LaravelAfip;

use litvinjuan\LaravelAfip\Commands\InstallLaravelAfipCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelAfipServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-afip')
            ->hasConfigFile()
            ->hasCommand(InstallLaravelAfipCommand::class);
    }
}
