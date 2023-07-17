<?php

namespace litvinjuan\LaravelAfip\Exceptions;

use Exception;

class AfipException extends Exception
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct("AFIP Error: {$message} ({$code})", $code, $previous);
    }
}
