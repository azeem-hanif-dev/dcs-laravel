<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'verify.jwt' => \App\Http\Middleware\Api\VerifyJwt::class,
            'auth.user' => \App\Http\Middleware\Api\AuthenticateUser::class,
            'company.filter' => \App\Http\Middleware\Api\CompanyFilter::class,
            'check.permission' => \App\Http\Middleware\Api\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
