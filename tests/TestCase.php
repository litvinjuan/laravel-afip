<?php

namespace litvinjuan\LaravelAfip\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use litvinjuan\LaravelAfip\LaravelAfipServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'litvinjuan\\LaravelAfip\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelAfipServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-afip_table.php.stub';
        $migration->up();
        */
    }
}
