<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleIdMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            abort(401, 'Usuario no autenticado.');
        }

        $idRol = (int) (Auth::user()->id_rol ?? 0);
        $rolesPermitidos = array_map('intval', $roles);

        if (!in_array($idRol, $rolesPermitidos, true)) {
            abort(403, 'No autorizado.');
        }

        return $next($request);
    }
}
