<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class SetTenantContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $teamId = auth()->user()->shop_id ?? 0;
            app(PermissionRegistrar::class)->setPermissionsTeamId($teamId);
        }

        return $next($request);
    }
}
