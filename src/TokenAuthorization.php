<?php

namespace litvinjuan\LaravelAfip;

use Carbon\Carbon;

class TokenAuthorization
{
    public function __construct(private readonly string $token, private readonly string $sign, private readonly Carbon $expiresAt)
    {
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getSign()
    {
        return $this->sign;
    }

    public function getExpiresAt(): Carbon
    {
        return $this->expiresAt;
    }
}
