<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginFormTipo(string $tipo)
    {
        return view('auth.login', ['tipo' => $tipo]);
    }

    public function loginTipo(Request $request, string $tipo)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        try {
            $res = DB::select('CALL SEL_LOGIN(?, ?)', [$request->email, '']);
            $r = $res[0] ?? null;

            if (!$r || $r->resultado !== 'OK' || !Hash::check($request->password, (string)$r->pass_hash)) {
                return back()->withErrors(['email' => 'Credenciales inválidas.'])->withInput();
            }

            // Autenticación manual con el ID del SP
            $user = User::find((int)$r->id_usuario) ?? new User();
            if (!$user->exists) {
                $user->setAttribute('id_usuario', (int)$r->id_usuario);
                $user->exists = true;
            }

            Auth::login($user);
            $request->session()->regenerate();

            // Guardamos datos en sesión
            session([
                'rol_texto' => strtolower(trim($r->rol ?? '')),
                'tipo_usuario' => strtolower(trim($r->tipo_usuario ?? ''))
            ]);

            // REDIRECCIONES SEGÚN TUS RUTAS
            if (session('tipo_usuario') === 'estudiante') {
                return redirect()->route('dashboard');
            }

            return redirect()->route('empleado.dashboard');

        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['email' => 'Error de servidor.'])->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();


        return redirect()->route('login');
    }
}
