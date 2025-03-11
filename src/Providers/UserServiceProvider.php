<?php

namespace Js\Authenticator\Providers;

use Illuminate\Support\ServiceProvider;
use Js\Authenticator\Services\UserService;
use Js\Authenticator\Contracts\UserContract;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(UserContract::class, UserService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
