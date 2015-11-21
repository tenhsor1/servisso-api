<?php

namespace App\Providers;

use Tymon\JWTAuth\Providers\JWTAuthServiceProvider;
use App\Extensions\JWTAuth;
use Tymon\JWTAuth\Blacklist;
use Tymon\JWTAuth\JWTManager;
use Tymon\JWTAuth\PayloadFactory;
use Tymon\JWTAuth\Claims\Factory;
use Illuminate\Support\ServiceProvider;
use Tymon\JWTAuth\Commands\JWTGenerateCommand;
use Tymon\JWTAuth\Validators\PayloadValidator;

class JWTServiceProvider extends JWTAuthServiceProvider
{
    /**
     * Bind some Interfaces and implementations
     */
    protected function bootBindings()
    {
        $this->app['App\Extensions\JWTAuth'] = function ($app) {
            return $app['tymon.jwt.auth'];
        };

        $this->app['Tymon\JWTAuth\Providers\User\UserInterface'] = function ($app) {
            return $app['tymon.jwt.provider.user'];
        };

        $this->app['Tymon\JWTAuth\Providers\JWT\JWTInterface'] = function ($app) {
            return $app['tymon.jwt.provider.jwt'];
        };

        $this->app['Tymon\JWTAuth\Providers\Auth\AuthInterface'] = function ($app) {
            return $app['tymon.jwt.provider.auth'];
        };

        $this->app['Tymon\JWTAuth\Providers\Storage\StorageInterface'] = function ($app) {
            return $app['tymon.jwt.provider.storage'];
        };

        $this->app['Tymon\JWTAuth\JWTManager'] = function ($app) {
            return $app['tymon.jwt.manager'];
        };

        $this->app['Tymon\JWTAuth\Blacklist'] = function ($app) {
            return $app['tymon.jwt.blacklist'];
        };

        $this->app['Tymon\JWTAuth\PayloadFactory'] = function ($app) {
            return $app['tymon.jwt.payload.factory'];
        };

        $this->app['Tymon\JWTAuth\Claims\Factory'] = function ($app) {
            return $app['tymon.jwt.claim.factory'];
        };

        $this->app['Tymon\JWTAuth\Validators\PayloadValidator'] = function ($app) {
            return $app['tymon.jwt.validators.payload'];
        };
    }


    /**
     * Register the bindings for the main JWTAuth class
     */
    protected function registerJWTAuth()
    {
        $this->app['tymon.jwt.auth'] = $this->app->share(function ($app) {

            $auth = new JWTAuth(
                $app['tymon.jwt.manager'],
                $app['tymon.jwt.provider.user'],
                $app['tymon.jwt.provider.auth'],
                $app['request']
            );

            return $auth->setIdentifier($this->config('identifier'));
        });
    }
}
