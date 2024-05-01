<?php

namespace litvinjuan\LaravelAfip;

class AfipConfiguration
{
    private string $cuit;

    private string $certificate;

    private string $private_key;

    private ?string $private_key_passphrase;

    private bool $production_mode;

    private AfipCmsSigner $signer;

    public function __construct(string $cuit = null, string $certificate = null, string $private_key = null, string $private_key_passphrase = null, bool $production_mode = true)
    {
        $this->cuit = $cuit ?? config('afip.cuit');
        $this->certificate = $certificate ?? config('afip.certificate');
        $this->private_key = $private_key ?? config('afip.key');
        $this->private_key_passphrase = $private_key_passphrase ?? config('afip.key-passphrase');
        $this->production_mode = $production_mode ?? config('afip.production', false);

        $this->signer = new AfipCmsSigner(
            $this->certificate,
            $this->private_key,
            $this->private_key_passphrase
        );
    }

    public function getSigner(): AfipCmsSigner
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

    public function getPublicIdentifier(): string
    {
        $privateKey = openssl_pkey_get_private($this->private_key, $this->private_key_passphrase);
        $publicKey = openssl_pkey_get_details($privateKey)['key'];

        return hash('sha256', $publicKey);
    }
}
