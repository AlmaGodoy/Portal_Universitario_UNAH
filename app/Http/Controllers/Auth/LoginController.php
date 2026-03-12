<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Bitacora;
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

    /**
     * Normaliza el email (minúsculas y sin espacios)
     */
    private function normalizeEmail(Request $request): void
    {
        $email = strtolower(trim((string)$request->input('email')));
        $request->merge(['email' => $email]);
    }

    private function throttleKey(Request $request): string
    {
        // ya viene normalizado por normalizeEmail()
        return (string)$request->input('email').'|'.$request->ip();
    }

    /**
     * Log técnico (tabla tbl_login_intentos)
     */
    private function logIntento(Request $request, string $resultado, ?string $detalle = null): void
    {
        try {
            DB::table('tbl_login_intentos')->insert([
                'email' => (string)$request->input('email'), // ya normalizado
                'ip' => $request->ip(),
                'user_agent' => substr((string)$request->userAgent(), 0, 255),
                'resultado' => $resultado,
                'detalle' => $detalle,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // no rompemos el login si falla el log técnico
        }
    }

    /**
     * Bitácora (tabla tbl_bitacora)
     */
    private function bitacora(?int $idUsuario, string $accion, string $descripcion): void
    {
        try {
            Bitacora::registrar(
                (int)($idUsuario ?? 0),
                $accion,
                $descripcion
            );
        } catch (\Throwable $e) {
            // no rompemos el login si falla bitácora
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
        // ✅ 1) normalizar email ANTES de validar / throttle / SP
        $this->normalizeEmail($request);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $key = $this->throttleKey($request);

        // 🔒 Bloqueo por RateLimiter
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            $this->logIntento($request, 'BLOQUEADO_THROTTLE', "Esperar {$seconds}s");
            $this->bitacora(
                null,
                'login_fallido',
                'Bloqueado por demasiados intentos. IP: '.$request->ip().' | Email: '.$request->email
            );

            return back()->withErrors([
                'email' => "Demasiados intentos fallidos. Espera " . $this->formatWait($seconds) . " antes de intentar nuevamente."
            ])->withInput();
        }

        try {
            $res = DB::select('CALL SEL_LOGIN(?, ?)', [
                $request->email,
                '' // tu SP no debe comparar la contraseña
            ]);

            if (empty($res) || !isset($res[0]->resultado)) {
                RateLimiter::hit($key, 300);
                $this->logIntento($request, 'CREDENCIALES_INVALIDAS', 'Respuesta inválida SP');
                $this->bitacora(null, 'login_fallido', 'Respuesta inválida del SP. Email: '.$request->email);

                return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
            }

            $r = $res[0];

            // ⛔ Si el SP rechaza
            if ($r->resultado !== 'OK') {
                RateLimiter::hit($key, 300);
                $this->logIntento($request, 'SP_RECHAZO', (string)$r->resultado);

                $spMsg = (string)$r->resultado;
                if (str_contains(strtolower($spMsg), 'no') && str_contains(strtolower($spMsg), 'ex')) {
                    $this->bitacora(null, 'intento_login_correo_inexistente', 'Correo no existe: '.$request->email.' | IP: '.$request->ip());
                } else {
                    $this->bitacora(null, 'login_fallido', 'SP rechazó: '.$spMsg.' | Email: '.$request->email);
                }

                return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
            }

            if (!isset($r->pass_hash) || !Hash::check($request->password, (string)$r->pass_hash)) {
                RateLimiter::hit($key, 300);
                $this->logIntento($request, 'CONTRASENA_INCORRECTA', 'Hash::check falló');

                $this->bitacora(
                    isset($r->id_usuario) ? (int)$r->id_usuario : null,
                    'login_fallido',
                    'Contraseña incorrecta. Email: '.$request->email.' | IP: '.$request->ip()
                );

                return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
            }

            RateLimiter::clear($key);

            $user = User::find($r->id_usuario);
            if (!$user) {
                $this->logIntento($request, 'USUARIO_NO_EXISTE', 'No existe en tbl_usuario');
                $this->bitacora(null, 'intento_login_correo_inexistente', 'No existe usuario para: '.$request->email.' | IP: '.$request->ip());

                return back()->withErrors(['email' => 'Usuario no encontrado'])->withInput();
            }

            $tipoElegido = session('login_tipo');
            $idRol = (int)($user->id_rol ?? 0);

            if ($tipoElegido === 'estudiante' && $idRol !== 2) {
                $this->logIntento($request, 'PORTAL_INCORRECTO', 'login estudiante sin rol 2');
                $this->bitacora((int)$user->id_usuario, 'login_fallido', 'Portal incorrecto (estudiante). Rol: '.$idRol);

                return back()->withErrors(['email' => 'Este portal es solo para estudiantes.'])->withInput();
            }

            if ($tipoElegido === 'empleado' && !in_array($idRol, [4, 5], true)) {
                $this->logIntento($request, 'PORTAL_INCORRECTO', 'login empleado sin rol 4/5');
                $this->bitacora((int)$user->id_usuario, 'login_fallido', 'Portal incorrecto (empleado). Rol: '.$idRol);

                return back()->withErrors(['email' => 'Este portal es solo para coordinador o secretario.'])->withInput();
            }

            $last2fa = null;
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
                $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                DB::table('tbl_login_autentications')
                    ->where('id_usuario', $user->id_usuario)
                    ->where('tipo', 'two_factor')
                    ->delete();

                DB::table('tbl_login_autentications')->insert([
                    'id_usuario' => $user->id_usuario,
                    'tipo' => 'two_factor',
                    'valor_hash' => hash('sha256', $code),
                    'expires_at' => now()->addMinutes(10),
                    'used_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                try {
                    Mail::to($request->email)->send(new TwoFactorCodeMail($code));
                    $this->logIntento($request, '2FA_ENVIADO', 'Se envió código 2FA');
                    $this->bitacora((int)$user->id_usuario, '2fa_enviado', 'Código 2FA enviado a: '.$request->email);
                } catch (\Throwable $e) {
                    $this->logIntento($request, '2FA_FALLO_ENVIO', $e->getMessage());
                    $this->bitacora((int)$user->id_usuario, '2fa_fallido', 'Fallo envío 2FA: '.$e->getMessage());

                    return back()->withErrors(['email' => 'No se pudo enviar el código 2FA. Intenta más tarde.'])->withInput();
                }

                session([
                    'twofa_user_id' => $user->id_usuario,
                    'twofa_login_tipo' => $tipoElegido,
                ]);

                return redirect()->route('twofa.form')
                    ->with('status', 'Te enviamos un código de 6 dígitos a tu correo.');
            }

            Auth::login($user);
            $request->session()->regenerate();

            session([
                'persona_id' => $r->id_persona ?? null,
                'rol_texto' => $r->rol ?? null,
                'tipo_usuario' => $r->tipo_usuario ?? null,
            ]);

            $this->logIntento($request, 'OK', 'Login exitoso (sin 2FA)');
            $this->bitacora((int)$user->id_usuario, 'login_exitoso', 'Inicio de sesión exitoso.');

            return match ($idRol) {
                2 => redirect()->intended('/panel-estudiante'),
                4 => redirect()->intended('/panel-coordinador'),
                5 => redirect()->intended('/panel-secretario'),
                default => redirect()->intended('/home'),
            };
        } catch (\Exception $e) {
            RateLimiter::hit($key, 300);
            $this->logIntento($request, 'EXCEPTION', $e->getMessage());
            $this->bitacora(null, 'login_fallido', 'EXCEPTION login: '.$e->getMessage().' | Email: '.$request->email);

            return back()->withErrors([
                'email' => 'Ocurrió un error al iniciar sesión. Intenta de nuevo.'
            ])->withInput();
        }
    }

    public function logout(Request $request)
    {
        try {
            $idUsuario = Auth::id();
            if ($idUsuario) {
                $this->bitacora((int)$idUsuario, 'logout', 'Cierre de sesión.');
            }
        } catch (\Throwable $e) {
            // no romper logout
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal');
    }
}
