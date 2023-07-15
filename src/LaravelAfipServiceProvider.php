<?php

namespace litvinjuan\LaravelAfip;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelAfipServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-afip')
            ->hasConfigFile()
            ->hasInstallCommand(function () {
                Storage::disk(config('afip.certificates-disk'))->makeDirectory(config('afip.certificates-directory', ''));
                Storage::disk(config('afip.certificates-disk'))->put(config('afip.certificates-directory'). '/cert', '');
                Storage::disk(config('afip.certificates-disk'))->put(config('afip.certificates-directory'). '/key', '');
            });
    }
}
