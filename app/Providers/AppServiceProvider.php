<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            $forwardedProto = (string) request()->headers->get('x-forwarded-proto', '');
            $httpsServerVar = (string) request()->server->get('HTTPS', '');

            $isHttpsBehindProxy = strtolower($forwardedProto) === 'https'
                || strtolower($httpsServerVar) === 'on'
                || $httpsServerVar === '1';

            if ($isHttpsBehindProxy) {
                URL::forceScheme('https');
                $this->app['request']->server->set('HTTPS', 'on');
            }
        }

        Gate::define('admin', fn($user) => $user->role === 'admin');
        Gate::define('staff', fn($user) => in_array($user->role, ['admin', 'staff']));
    }
}