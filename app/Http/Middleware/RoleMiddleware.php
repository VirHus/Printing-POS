<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {

        if (!auth()->check()) {
            return redirect()->route('login');
        }


        /*
        |--------------------------------------------------------------------------
        | Check Account Status First
        |--------------------------------------------------------------------------
        */

        if (!auth()->user()->is_active) {

            auth()->logout();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' =>
                        'Your account has been deactivated.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Check Role
        |--------------------------------------------------------------------------
        */

        if (!in_array(
            auth()->user()->role,
            $roles
        )) {

            abort(
                403,
                'You are not authorized to access this page.'
            );
        }


        return $next($request);
    }
}