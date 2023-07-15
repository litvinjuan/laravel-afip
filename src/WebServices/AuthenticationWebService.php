<?php

namespace litvinjuan\LaravelAfip\WebServices;

use litvinjuan\LaravelAfip\Enum\AfipService;

class AuthenticationWebService extends WebService
{
    protected function getAfipService(): AfipService
    {
        return AfipService::wsaa;
    }

    public function login(string $cms): mixed
    {
        return $this->call('loginCms', [
            'in0' => $this->cms,
        ]);
    }
}
