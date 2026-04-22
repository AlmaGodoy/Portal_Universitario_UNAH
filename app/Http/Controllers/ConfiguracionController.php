<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('portal');
        }

        $rolTexto = strtolower(trim((string) session('rol_texto', '')));
        $loginTipo = strtolower(trim((string) session('login_tipo', '')));

        $layout = $this->resolverLayout($loginTipo, $rolTexto);
        $dashboardUrl = $this->resolverDashboardUrl($loginTipo, $rolTexto);
        $portalTexto = $this->resolverPortalTexto($loginTipo, $rolTexto);

        return view('configuracion', [
            'user' => $user,
            'layout' => $layout,
            'dashboardUrl' => $dashboardUrl,
            'portalTexto' => $portalTexto,
        ]);
    }

    public function perfil()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'nombre' => optional($user->persona)->nombre_persona
                    ?? $user->name
                    ?? $user->nombre
                    ?? 'Usuario',
                'correo' => optional($user->persona)->correo_institucional
                    ?? $user->email
                    ?? $user->correo_institucional
                    ?? '',
                'tipo_usuario' => optional($user->persona)->tipo_usuario
                    ?? session('login_tipo')
                    ?? 'usuario',
                'estado_cuenta' => $user->estado_cuenta ?? 1,
                'rol_actual' => session('rol_texto') ?? 'usuario',
            ],
        ]);
    }

    public function actualizarPerfil(Request $request)
    {
        return response()->json([
            'ok' => true,
            'message' => 'Perfil actualizado correctamente.',
        ]);
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => ['required'],
            'password_nueva' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&._\-]/',
                'confirmed',
            ],
        ], [
            'password_actual.required' => 'Debes ingresar tu contraseña actual.',
            'password_nueva.required' => 'Debes ingresar una nueva contraseña.',
            'password_nueva.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password_nueva.regex' => 'La nueva contraseña debe incluir mayúscula, minúscula, número y símbolo.',
            'password_nueva.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('portal')->with('error', 'Sesión no válida.');
        }

        if (!Hash::check($request->password_actual, $user->getAuthPassword())) {
            return back()
                ->withErrors(['password_actual' => 'La contraseña actual es incorrecta.'])
                ->withInput();
        }

        $user->password = Hash::make($request->password_nueva);

        try {
            $user->save();

            return back()->with('success', '¡Éxito! Tu contraseña fue actualizada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Hubo un problema al guardar en la base de datos.');
        }
    }

    private function resolverLayout(string $loginTipo, string $rolTexto): string
    {
        if ($loginTipo !== 'empleado') {
            return 'layouts.app-estudiantes';
        }

        if ($this->esRolAcademica($rolTexto)) {
            return 'layouts.app-secretaria-academica';
        }

        if ($this->esRolCoordinacion($rolTexto)) {
            return 'layouts.app-coordinador';
        }

        if ($this->esRolSecretariaCarrera($rolTexto)) {
            return 'layouts.app-secretaria';
        }

        return 'layouts.app-coordinador';
    }

    private function resolverDashboardUrl(string $loginTipo, string $rolTexto): string
    {
        if ($loginTipo !== 'empleado') {
            return route('dashboard');
        }

        return route('empleado.dashboard');
    }

    private function resolverPortalTexto(string $loginTipo, string $rolTexto): string
    {
        if ($loginTipo !== 'empleado') {
            return 'portal estudiantil';
        }

        if ($this->esRolAcademica($rolTexto)) {
            return 'panel de secretaría académica';
        }

        if ($this->esRolCoordinacion($rolTexto)) {
            return 'panel de coordinación';
        }

        if ($this->esRolSecretariaCarrera($rolTexto)) {
            return 'panel de secretaría de carrera';
        }

        return 'panel de empleado';
    }

    private function esRolAcademica(string $rolTexto): bool
    {
        return
            str_contains($rolTexto, 'secretaria_general') ||
            str_contains($rolTexto, 'secretaría_general') ||
            str_contains($rolTexto, 'secretaria general') ||
            str_contains($rolTexto, 'secretaría general') ||
            str_contains($rolTexto, 'secretaria academica') ||
            str_contains($rolTexto, 'secretaría academica') ||
            str_contains($rolTexto, 'secretaria académica') ||
            str_contains($rolTexto, 'secretaría académica') ||
            str_contains($rolTexto, 'academica') ||
            str_contains($rolTexto, 'académica');
    }

    private function esRolCoordinacion(string $rolTexto): bool
    {
        return
            str_contains($rolTexto, 'coordinador') ||
            str_contains($rolTexto, 'coordinadora');
    }

    private function esRolSecretariaCarrera(string $rolTexto): bool
    {
        return
            str_contains($rolTexto, 'secretaria de carrera') ||
            str_contains($rolTexto, 'secretaría de carrera') ||
            str_contains($rolTexto, 'secretaria_carrera') ||
            str_contains($rolTexto, 'secretaría_carrera') ||
            (
                str_contains($rolTexto, 'secretar') &&
                !$this->esRolAcademica($rolTexto)
            );
    }
}
