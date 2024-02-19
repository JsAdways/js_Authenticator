<?php

namespace Js\Authenticator\Facades;

use Illuminate\Support\Facades\Facade;
use Js\Authenticator\Contracts\AuthContract;

class AuthFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AuthContract::class;
    }
}