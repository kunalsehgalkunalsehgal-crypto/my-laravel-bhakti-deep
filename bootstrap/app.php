<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuthenticate::class,
            'admin.guest' => \App\Http\Middleware\RedirectIfAdminAuthenticated::class,
            'admin.permission' => \App\Http\Middleware\EnsureAdminHasPermission::class,
            'pandit.nocache' => \App\Http\Middleware\NoCachePanditPages::class,
        ]);
        //bhai is me na dekhoki ye mane live karne se pehle delee karna ha 
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
    'zoom/webhook',
    'payments/razorpay/webhook',
]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
