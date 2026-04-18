<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RolMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            abort(401, 'Usuario no autenticado.');
        }

        $user = Auth::user();
        $rolActual = null;

        if (session()->has('rol_texto')) {
            $rolActual = strtolower(trim((string) session('rol_texto')));
        } elseif (!empty($user->rol_texto)) {
            $rolActual = strtolower(trim((string) $user->rol_texto));
        } elseif (!empty($user->role)) {
            $rolActual = strtolower(trim((string) $user->role));
        }

        $rolesPermitidos = array_map(function ($rol) {
            return strtolower(trim((string) $rol));
        }, $roles);

        if (!$rolActual || !in_array($rolActual, $rolesPermitidos, true)) {
            abort(403, 'No autorizado.');
        }

        return $next($request);
    }
}
