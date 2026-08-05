<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerfilSecretariaController extends Controller
{
    /**
     * Muestra la vista del perfil usando el layout de Secretaría de Carrera.
     */
    public function index(): View|RedirectResponse
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        if (method_exists($usuario, 'persona')) {
            $usuario->loadMissing('persona');
        }

        return view('perfil_secretaria', [
            'usuario' => $usuario,
        ]);
    }
}
