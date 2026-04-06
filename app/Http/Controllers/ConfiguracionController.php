<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Asegúrate de que esta línea esté presente

class ConfiguracionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('configuracion', compact('user'));
    }

    public function actualizarPassword(Request $request)
    {
        // 1. Validación de reglas
        $request->validate([
            'password_actual' => ['required'],
            'password_nueva' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',      // al menos una mayúscula
                'regex:/[a-z]/',      // al menos una minúscula
                'regex:/[0-9]/',      // al menos un número
                'regex:/[@$!%*#?&._-]/', // al menos un símbolo
                'confirmed',
            ],
        ], [
            'password_actual.required' => 'Debes ingresar tu contraseña actual.',
            'password_nueva.required' => 'Debes ingresar una nueva contraseña.',
            'password_nueva.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password_nueva.regex' => 'La nueva contraseña debe incluir mayúscula, minúscula, número y símbolo.',
            'password_nueva.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        /** @var \App\Models\User $user */ // <--- ESTO QUITA LA LÍNEA ROJA
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('portal')->with('error', 'Sesión no válida.');
        }

        // 2. Verificar contraseña actual usando el Hash del sistema
        if (!Hash::check($request->password_actual, $user->getAuthPassword())) {
            return back()->withErrors(['password_actual' => 'La contraseña actual es incorrecta.'])->withInput();
        }

        // 3. Actualización de la columna 'password'
        $user->password = Hash::make($request->password_nueva);
        
        try {
            $user->save();
            return back()->with('success', '¡Éxito! Tu contraseña fue actualizada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Hubo un problema al guardar en la base de datos.');
        }
    }
}