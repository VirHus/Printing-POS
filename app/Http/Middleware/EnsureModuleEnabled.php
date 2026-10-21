<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->isSuperadmin()) {
            $module = SystemSetting::moduleForRoute($request->route()?->getName() ?? '');
            if ($module !== null && ! SystemSetting::current()->moduleEnabled($module, $request->user())) {
                if ($module === 'dashboard' && $request->isMethod('GET')) {
                    return redirect()->route(SystemSetting::current()->homeRoute($request->user()));
                }
                abort(403, 'This module is disabled for your role. Please contact your administrator.');
            }
        }

        return $next($request);
    }
}
