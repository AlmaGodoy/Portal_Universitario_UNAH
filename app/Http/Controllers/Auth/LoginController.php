<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginFormTipo(string $tipo)
    {
        session(['login_tipo' => $tipo]);
        return view('auth.login', ['tipo' => $tipo]);
    }

    public function loginTipo(Request $request, string $tipo)
    {
        session(['login_tipo' => $tipo]);
        return $this->login($request);
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower((string)$request->input('email')).'|'.$request->ip();
    }

    private function logIntento(Request $request, string $resultado, ?string $detalle = null): void
    {
        try {
            DB::table('tbl_login_intentos')->insert([
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'user_agent' => substr((string)$request->userAgent(), 0, 255),
                'resultado' => $resultado,
                'detalle' => $detalle,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // no rompemos el login si falla el log
        }
    }

    private function formatWait(int $seconds): string
    {
        $m = intdiv($seconds, 60);
        $s = $seconds % 60;

        if ($m <= 0) return "{$s} segundos";
        if ($s === 0) return "{$m} minuto(s)";
        return "{$m} minuto(s) y {$s} segundo(s)";
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $key = $this->throttleKey($request);

        // 🔒 Bloqueo al llegar al límite (5 intentos)
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->logIntento($request, 'BLOQUEADO_THROTTLE', "Esperar {$seconds}s");

            return back()->withErrors([
                'email' => "Demasiados intentos fallidos. Espera ".$this->formatWait($seconds)." antes de intentar nuevamente."
            ])->withInput();
        }

        try {
            // ✅ SP SOLO BUSCA y DEVUELVE pass_hash (bcrypt)
            $res = DB::select('CALL SEL_LOGIN(?, ?)', [
                $request->email,
                '' // o $request->password si tu SP aún lo exige (pero NO debe compararla)
            ]);

            if (empty($res) || !isset($res[0]->resultado)) {
                RateLimiter::hit($key, 300);
                $this->logIntento($request, 'CREDENCIALES_INVALIDAS', 'Respuesta inválida SP');

                return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
            }

            $r = $res[0];

            if ($r->resultado !== 'OK') {
                RateLimiter::hit($key, 300);
                $this->logIntento($request, 'SP_RECHAZO', (string)$r->resultado);

                return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
            }

            // ✅ Validar password con bcrypt
            if (!isset($r->pass_hash) || !Hash::check($request->password, (string)$r->pass_hash)) {
                RateLimiter::hit($key, 300);
                $this->logIntento($request, 'CONTRASENA_INCORRECTA', 'Hash::check falló');

                return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
            }

            // ✅ Limpiar intentos porque contraseña fue correcta
            RateLimiter::clear($key);

            // ✅ cargar user
            $user = User::find($r->id_usuario);
            if (!$user) {
                $this->logIntento($request, 'USUARIO_NO_EXISTE', 'No existe en tbl_usuario');
                return back()->withErrors(['email' => 'Usuario no encontrado'])->withInput();
            }

            // ✅ Validación portal (estudiante / empleado)
            $tipoElegido = session('login_tipo'); // estudiante|empleado
            $idRol = (int)($user->id_rol ?? 0);

            if ($tipoElegido === 'estudiante' && $idRol !== 2) {
                $this->logIntento($request, 'PORTAL_INCORRECTO', 'login estudiante sin rol 2');
                return back()->withErrors(['email' => 'Este portal es solo para estudiantes.'])->withInput();
            }

            if ($tipoElegido === 'empleado' && !in_array($idRol, [4, 5], true)) {
                $this->logIntento($request, 'PORTAL_INCORRECTO', 'login empleado sin rol 4/5');
                return back()->withErrors(['email' => 'Este portal es solo para coordinador o secretario.'])->withInput();
            }

            // ============================
            // ✅ 2FA: primera vez o cada 30 días
            // ============================
            $last2fa = null;

            // si tu modelo no trae el campo, igual lo leemos directo con DB
            try {
                $last2fa = $user->twofa_verified_at ?? null;
            } catch (\Throwable $e) {
                $last2fa = null;
            }

            if (!$last2fa) {
                $last2fa = DB::table('tbl_usuario')
                    ->where('id_usuario', $user->id_usuario)
                    ->value('twofa_verified_at');
            }

            $needs2fa = !$last2fa || now()->parse($last2fa)->lt(now()->subDays(30));

            if ($needs2fa) {
                // generar 6 dígitos
                $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                DB::table('two_factor_codes')->insert([
                    'id_usuario' => $user->id_usuario,
                    'code_hash' => hash('sha256', $code),
                    'expires_at' => now()->addMinutes(10),
                    'used_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // enviar correo
                Mail::to($request->email)->send(new TwoFactorCodeMail($code));

                // guardar sesión temporal para la pantalla 2FA
                session([
                    'twofa_user_id' => $user->id_usuario,
                    'twofa_login_tipo' => $tipoElegido,
                ]);

                $this->logIntento($request, '2FA_ENVIADO', 'Se envió código 2FA');

                return redirect()->route('twofa.form')
                    ->with('status', 'Te enviamos un código de 6 dígitos a tu correo.');
            }

            // ============================
            // ✅ si NO necesita 2FA: entra normal
            // ============================
            Auth::login($user);
            $request->session()->regenerate();

            session([
                'persona_id' => $r->id_persona ?? null,
                'rol_texto' => $r->rol ?? null,
                'tipo_usuario' => $r->tipo_usuario ?? null,
            ]);

            $this->logIntento($request, 'OK', 'Login exitoso (sin 2FA)');

            return match ($idRol) {
                2 => redirect()->intended('/panel-estudiante'),
                4 => redirect()->intended('/panel-coordinador'),
                5 => redirect()->intended('/panel-secretario'),
                default => redirect()->intended('/home'),
            };

        } catch (\Exception $e) {
            RateLimiter::hit($key, 300);
            $this->logIntento($request, 'EXCEPTION', $e->getMessage());

            return back()->withErrors([
                'email' => 'Ocurrió un error al iniciar sesión. Intenta de nuevo.'
            ])->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal');
    }
}
