<?php

namespace litvinjuan\LaravelAfip\Exceptions;

use Exception;

class AfipSoapException extends Exception
{
    private readonly Exception $original_exception;

    public function __construct(Exception $original_exception)
    {
        $this->original_exception = $original_exception;

        parent::__construct($this->original_exception->getMessage(), $this->original_exception->getCode(), $this->original_exception->getPrevious());
    }

    public function getOriginalException(): Exception
    {
        return $this->original_exception;
    }
}
