<?php

namespace Js\Authenticator\Facades;

use Illuminate\Support\Facades\Facade;
use Js\Authenticator\Contracts\UserContract;

class UserFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UserContract::class;
    }
}