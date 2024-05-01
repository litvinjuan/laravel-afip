<?php

namespace litvinjuan\LaravelAfip;

class AfipConfiguration
{
    private string $cuit;

    private string $certificate;

    private string $private_key;

    private ?string $private_key_passphrase;

    private bool $production_mode;

    private AfipSigner $signer;

    public function __construct(string $cuit = null, string $certificate = null, string $private_key = null, string $private_key_passphrase = null, bool $production_mode = true)
    {
        $this->cuit = $cuit ?? config('afip.cuit');
        $this->certificate = $certificate ?? config('afip.certificate');
        $this->private_key = $private_key ?? config('afip.key');
        $this->private_key_passphrase = $private_key_passphrase ?? config('afip.key-passphrase');
        $this->production_mode = $production_mode ?? config('afip.production', false);

        $this->signer = new AfipSigner(
            $this->certificate,
            $this->private_key,
            $this->private_key_passphrase
        );
    }

    public function getSigner(): AfipSigner
    {
        return $this->signer;
    }

    public function isProduction(): bool
    {
        return $this->production_mode;
    }

    public function getCuit(): string
    {
        return $this->cuit;
    }
}
