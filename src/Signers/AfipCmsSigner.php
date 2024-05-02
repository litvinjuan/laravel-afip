<?php

namespace litvinjuan\LaravelAfip\Signers;

use File;
use Illuminate\Support\Facades\Log;
use litvinjuan\LaravelAfip\Exceptions\AfipSigningException;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class AfipCmsSigner
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
            $result = openssl_cms_sign(
                $input_filename,
                $output_filename,
                $this->getCertificate(),
                [$this->getPrivateKey(), $this->getPrivateKeyPassphrase()],
                [],
                ! OPENSSL_CMS_DETACHED
            );

            if (! $result) {
                throw new AfipSigningException('There was an error while signing using the certificate and key');
            }
        } catch (\Exception $exception) {
            Log::error($exception);
            throw new AfipSigningException('There was an error while signing using the certificate and key');
        }

        $cms = $this->getCmsFromCmsOutputFile($output_filename);
        $temporaryDirectory->delete();

        return $cms;
    }

    private function getCmsFromCmsOutputFile(string $output_filename): string
    {
        $cms = File::get($output_filename);

        $lastHeader = 'Content-Transfer-Encoding: base64';
        $lastHeaderPosition = strpos($cms, $lastHeader);
        $lastHeaderLength = strlen($lastHeader);

        return trim(
            substr($cms, $lastHeaderPosition + $lastHeaderLength)
        );
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
