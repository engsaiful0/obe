<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        // Allow access to login and authentication routes
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        // Check if session is destroyed or invalid
        if (!$request->session()->isStarted() || !$request->session()->has('_token')) {
            Session::flush();
        }

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Determine if the request should pass through without authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldPassThrough($request): bool
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
        $path = $request->path();

        // List of routes and paths that should be accessible without authentication
        $publicRoutes = [
            'login',
            'auth-login-basic',
            'auth-login-basic.post',
            'auth-register-basic',
            'auth-register-cover',
            'auth-register-multisteps',
            'auth-verify-email-basic',
            'auth-verify-email-cover',
            'auth-reset-password-basic',
            'auth-reset-password-cover',
            'auth-forgot-password-cover',
            'auth-two-steps-basic',
            'auth-two-steps-cover',
            'api-bus-requisitions.store',
            'api-bus-requisitions.index',
            'api-departments.index',
        ];

        $publicPaths = [
            '/',
            '/login',
            '/auth/login-basic',
            '/auth/register-basic',
            '/auth/register-cover',
            '/auth/register-multisteps',
            '/auth/verify-email-basic',
            '/auth/verify-email-cover',
            '/auth/reset-password-basic',
            '/auth/reset-password-cover',
            '/auth/forgot-password-basic',
            '/auth/forgot-password-cover',
            '/auth/two-steps-basic',
            '/auth/two-steps-cover',
        ];

        // Allow API bus-requisitions endpoints without authentication
        if (str_starts_with($path, 'api/bus-requisitions') || str_starts_with($path, 'api/departments')) {
            return true;
        }

        return in_array($routeName, $publicRoutes) || in_array($path, $publicPaths);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Clear session if it's destroyed
        if (!$request->session()->isStarted() || !$request->session()->has('_token')) {
            Session::flush();
        }

        return $request->expectsJson() ? null : route('login');
    }
}
