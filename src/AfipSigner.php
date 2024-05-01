<?php

namespace litvinjuan\LaravelAfip;

use File;
use Illuminate\Support\Facades\Log;
use litvinjuan\LaravelAfip\Exceptions\AfipSigningException;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class AfipSigner
{
    private string $certificate;

    private string $private_key;

    private ?string $private_key_passphrase;

    public function __construct(string $certificate, string $private_key, ?string $private_key_passphrase = null)
    {
        $this->certificate = $certificate;
        $this->private_key = $private_key;
        $this->private_key_passphrase = $private_key_passphrase;
    }

    public function sign(string $input): string
    {
        $temporaryDirectory = $this->newTemporaryDirectory();

        $input_filename = $temporaryDirectory->path('input.tmp');
        $output_filename = $temporaryDirectory->path('output.tmp');

        File::put($input_filename, $input);

        try {
            $result = openssl_pkcs7_sign(
                $input_filename,
                $output_filename,
                $this->getCertificate(),
                [$this->getPrivateKey(), $this->getPrivateKeyPassphrase()],
                [],
                ! PKCS7_DETACHED
            );

            if (! $result) {
                throw new AfipSigningException('There was an error while signing using the certificate and key');
            }
        } catch (\Exception $exception) {
            Log::error($exception);
            throw new AfipSigningException('There was an error while signing using the certificate and key');
        }

        $file = fopen($output_filename, 'r');
        $i = 0;

        $signed = '';
        while (! feof($file)) {
            $buffer = fgets($file);
            if ($i++ >= 4) {
                $signed .= $buffer;
            }
        }
        fclose($file);

        $temporaryDirectory->delete();

        return $signed;
    }

    private function getCertificate()
    {
        return $this->certificate;
    }

    private function getPrivateKey()
    {
        return $this->private_key;
    }

    private function getPrivateKeyPassphrase()
    {
        return $this->private_key_passphrase;
    }

    private function newTemporaryDirectory()
    {
        return (new TemporaryDirectory())->force()->create();
    }
}
