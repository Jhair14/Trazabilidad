<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | TRUST PROXIES (Nginx Proxy Manager / HTTPS)
        |--------------------------------------------------------------------------
        */
        $middleware->trustProxies(
            at: '*',
            headers:
                Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_PROTO |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT
        );

        /*
        |--------------------------------------------------------------------------
        | API MIDDLEWARE
        |--------------------------------------------------------------------------
        */
        $middleware->api(prepend: [
            \App\Http\Middleware\Cors::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | WEB MIDDLEWARE
        |--------------------------------------------------------------------------
        */
        $middleware->web(prepend: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | ALIAS
        |--------------------------------------------------------------------------
        */
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {

                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json(['message' => 'Unauthenticated.'], 401);
                }

                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = $e->getMessage();

                if (config('app.env') === 'production' && $status === 500) {
                    $message = 'Internal server error';
                }

                return response()->json([
                    'message' => $message,
                    'error' => class_basename($e),
                ], $status);
            }
        });

        $exceptions->render(function (
            \Illuminate\Session\TokenMismatchException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->is('mis-pedidos') && $request->isMethod('post')) {
                return redirect()->route('mis-pedidos')
                    ->with('error', 'Su sesión ha expirado. Por favor, intente nuevamente.');
            }
        });
    })
    ->create();
