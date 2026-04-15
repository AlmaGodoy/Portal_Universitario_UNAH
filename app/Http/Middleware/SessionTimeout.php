<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    /**
     * 10 minutos = 600 segundos
     */
    private int $timeout = 1860;

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity_time');
            $currentTime = time();

            if ($lastActivity && ($currentTime - $lastActivity) > $this->timeout) {
                $loginTipo = session('login_tipo');

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($loginTipo === 'estudiante') {
                    return redirect()
                        ->route('login.tipo', ['tipo' => 'estudiante'])
                        ->withErrors([
                            'session' => 'La sesión expiró por inactividad. Inicia sesión nuevamente para continuar.'
                        ]);
                }

                if ($loginTipo === 'empleado') {
                    return redirect()
                        ->route('login.tipo', ['tipo' => 'empleado'])
                        ->withErrors([
                            'session' => 'La sesión expiró por inactividad. Inicia sesión nuevamente para continuar.'
                        ]);
                }

                return redirect('/portal')->withErrors([
                    'session' => 'La sesión expiró por inactividad. Inicia sesión nuevamente para continuar.'
                ]);
            }

            session(['last_activity_time' => $currentTime]);
        }

        return $next($request);
    }
}
