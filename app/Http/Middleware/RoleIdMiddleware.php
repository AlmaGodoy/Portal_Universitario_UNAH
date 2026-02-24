<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleIdMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $idRol = (int) (Auth::user()->id_rol ?? 0);
        $rolesPermitidos = array_map('intval', $roles);

        if (!in_array($idRol, $rolesPermitidos, true)) {
            abort(403, 'No autorizado');
        }

        return $next($request);
    }
}
