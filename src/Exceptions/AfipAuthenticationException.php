<?php

namespace litvinjuan\LaravelAfip\Exceptions;

use Exception;

class AfipAuthenticationException extends Exception
{
    public function __construct(private readonly Exception $original_exception)
    {
        parent::__construct('', 0, null);
    }

    public function getOriginalException()
    {
        return $this->original_exception;
    }
}
