<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Providers\RepositoryServiceProvider;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\NoCache;
use App\Http\Middleware\TrackRepeatedViolations;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        RepositoryServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'nocache' => NoCache::class,
            'track.violations' => TrackRepeatedViolations::class,
        ]);

        // NoCache removed from global web stack — it was forcing every response
        // (HTML, CSS, JS, images, AJAX) to never be cached by the browser,
        // causing full re-downloads on every page navigation.
        //
        // Use ->middleware('nocache') on individual routes that truly need it
        // (e.g. payment processing, real-time dashboards).
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
