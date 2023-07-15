<?php

namespace litvinjuan\LaravelAfip\Exceptions;

use Exception;

class AfipException extends Exception
{
    public function __construct(private readonly Exception $original_exception)
    {
        parent::__construct($this->original_exception->getMessage(), $this->original_exception->getCode(), $this->original_exception->getPrevious());
    }

    public function getOriginalException(): Exception
    {
        return $this->original_exception;
    }
}
