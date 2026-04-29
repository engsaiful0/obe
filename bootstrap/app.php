<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\CheckPermission;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->web(LocaleMiddleware::class);
    $middleware->alias([
        'permission' => CheckPermission::class,
    ]);
    
    // Apply authentication middleware globally
    // The Authenticate middleware will handle excluding login/auth routes
    $middleware->web(append: [
        \App\Http\Middleware\Authenticate::class,
    ]);
    
    // Exclude authentication routes and API routes from CSRF protection
    $middleware->validateCsrfTokens(except: [
        '/auth/login-basic',
        '/api/bus-requisitions',
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })->create();
