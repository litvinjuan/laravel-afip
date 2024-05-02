<?php

namespace litvinjuan\LaravelAfip\Authorization;

use Carbon\Carbon;

class TokenAuthorization
{
    private readonly string $token;
    private readonly string $sign;
    private readonly Carbon $expiresAt;

    public function __construct(string $token, string $sign, Carbon $expiresAt)
    {
        $this->token = $token;
        $this->sign = $sign;
        $this->expiresAt = $expiresAt;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getSign(): string
    {
        return $this->sign;
    }

    public function getExpiresAt(): Carbon
    {
        return $this->expiresAt;
    }
}
