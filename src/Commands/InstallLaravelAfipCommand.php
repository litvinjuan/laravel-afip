<?php

namespace litvinjuan\LaravelAfip\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class InstallLaravelAfipCommand extends Command
{
    public $signature = 'laravel-afip:install';

    public $description = 'Installs the laravel afip package';

    public function handle(): int
    {
        $this->comment('Generating storage directories');

        $this->generateDirectories();

        $this->comment('Done!');

        return self::SUCCESS;
    }

    private function generateDirectories()
    {
        Storage::disk(config('afip.certificates-disk'))->makeDirectory(config('afip.certificates-directory'));

        Storage::disk(config('afip.certificates-disk'))->put(config('afip.certificates-directory'). '/cert', '');
        Storage::disk(config('afip.certificates-disk'))->put(config('afip.certificates-directory'). '/key', '');
    }
}
