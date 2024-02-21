<?php

namespace Js\Authenticator\Providers;

use Illuminate\Support\ServiceProvider;
use Js\Authenticator\Middleware\JsAuthenticate;
use Js\Authenticator\Services\AuthService;
use Js\Authenticator\Contracts\AuthContract;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $configPath = __DIR__ . '/../../config/js_auth.php';
        $this->mergeConfigFrom($configPath, 'js_auth');

        $configPath = __DIR__ . '/../../config/forestage.php';
        $this->mergeConfigFrom($configPath, 'js_auth');

        $this->app->bind(AuthContract::class, AuthService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $source = realpath($raw = __DIR__.'/../../config/js_auth.php') ?: $raw;
        $this->publishes([
            $source => config_path('js_auth.php'),
        ]);

        $source = realpath($raw = __DIR__.'/../../config/forestage.php') ?: $raw;
        $this->publishes([
            $source => config_path('forestage.php'),
        ]);

        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->app->make('router')->aliasMiddleware('js-authenticate-middleware-alias', JsAuthenticate::class);
    }
}
