<?php

namespace litvinjuan\LaravelAfip;

use Illuminate\Support\Facades\Log;
use litvinjuan\LaravelAfip\Exceptions\AfipSigningException;

class AfipSigning
{
    public static function sign(string $input_file, string $output_file): string
    {
        try {
            $result = openssl_pkcs7_sign(
                $input_file,
                $output_file,
                self::getCert(),
                [self::getKey(), self::getPassphrase()],
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

        $file = fopen($output_file, 'r');
        $i = 0;

        $cms = '';
        while (! feof($file)) {
            $buffer = fgets($file);
            if ($i++ >= 4) {
                $cms .= $buffer;
            }
        }
        fclose($file);

        return $cms;
    }

    private static function getCert()
    {
        return config('afip.certificate');
    }

    private static function getKey()
    {
        return config('afip.key');
    }

    private static function getPassphrase()
    {
        return config('afip.key-passphrase');
    }
}
