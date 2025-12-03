<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        if ($permission && !\Illuminate\Support\Facades\Gate::check($permission)) {
            abort(403, 'No tiene permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
