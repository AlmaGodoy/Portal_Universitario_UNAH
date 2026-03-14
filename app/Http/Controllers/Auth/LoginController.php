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

    private function normalizeEmail(Request $request): void
    {
        $email = strtolower(trim((string) $request->input('email')));
        $request->merge(['email' => $email]);
    }

    private function throttleKey(Request $request): string
    {
        return (string) $request->input('email') . '|' . $request->ip();
    }

    private function logIntento(Request $request, string $resultado, ?string $detalle = null): void
    {
        try {
            DB::table('tbl_login_intentos')->insert([
                'email' => (string) $request->input('email'),
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'resultado' => $resultado,
                'detalle' => $detalle,
                'fecha' => now(),
            ]);
        } catch (\Throwable $e) {
            //
        }
    }

    private function registrarBitacora(?int $idUsuario, string $accion, string $descripcion, ?int $idObjeto = null): void
    {
        try {
            DB::table('tbl_bitacora')->insert([
                'id_usuario' => $idUsuario,
                'id_objeto' => $idObjeto,
                'accion' => $accion,
                'fecha_accion' => now(),
                'descripcion' => $descripcion,
            ]);
        } catch (\Throwable $e) {
            //
        }
    }

    private function formatWait(int $seconds): string
    {
        $m = intdiv($seconds, 60);
        $s = $seconds % 60;

        if ($m <= 0) {
            return "{$s} segundos";
        }

        if ($s === 0) {
            return "{$m} minuto(s)";
        }

        return "{$m} minuto(s) y {$s} segundo(s)";
    }

    public function login(Request $request)
    {
        $this->normalizeEmail($request);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            $this->logIntento($request, 'BLOQUEADO_THROTTLE', "Esperar {$seconds}s");
            $this->registrarBitacora(
                null,
                'login_fallido',
                'Bloqueado por demasiados intentos. IP: ' . $request->ip() . ' | Email: ' . $request->email
            );

            return back()->withErrors([
                'email' => 'Demasiados intentos fallidos. Espera ' . $this->formatWait($seconds) . ' antes de intentar nuevamente.'
            ])->withInput();
        }

        try {
            $res = DB::select('CALL SEL_LOGIN(?, ?)', [
                $request->email,
                ''
            ]);

            $r = $res[0] ?? null;

            if (!$r || !isset($r->resultado)) {
                RateLimiter::hit($key, 300);
                $this->logIntento($request, 'CREDENCIALES_INVALIDAS', 'Respuesta inválida SP');
                $this->registrarBitacora(null, 'login_fallido', 'Respuesta inválida del SP. Email: ' . $request->email);

                return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
            }

            if ($r->resultado !== 'OK') {
                RateLimiter::hit($key, 300);
                $this->logIntento($request, 'SP_RECHAZO', (string) $r->resultado);

                $spMsg = (string) $r->resultado;

                if ($spMsg === 'USUARIO_NO_EXISTE') {
                    $this->registrarBitacora(
                        null,
                        'intento_login_correo_inexistente',
                        'Correo no existe: ' . $request->email . ' | IP: ' . $request->ip()
                    );
                } else {
                    $this->registrarBitacora(
                        null,
                        'login_fallido',
                        'SP rechazó: ' . $spMsg . ' | Email: ' . $request->email
                    );
                }

                return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
            }

            if (!isset($r->pass_hash) || !Hash::check($request->password, (string) $r->pass_hash)) {
                RateLimiter::hit($key, 300);
                $this->logIntento($request, 'CONTRASENA_INCORRECTA', 'Hash::check falló');

                $this->registrarBitacora(
                    isset($r->id_usuario) ? (int) $r->id_usuario : null,
                    'login_fallido',
                    'Contraseña incorrecta. Email: ' . $request->email . ' | IP: ' . $request->ip()
                );

                return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
            }

            RateLimiter::clear($key);

            $user = User::find($r->id_usuario);

            if (!$user) {
                $this->logIntento($request, 'USUARIO_NO_EXISTE', 'No existe en tbl_usuario');
                $this->registrarBitacora(
                    null,
                    'intento_login_correo_inexistente',
                    'No existe usuario para: ' . $request->email . ' | IP: ' . $request->ip()
                );

                return back()->withErrors(['email' => 'Usuario no encontrado'])->withInput();
            }

            $tipoElegido = session('login_tipo');
            $idRol = (int) ($r->id_rol ?? 0);

            if ($tipoElegido === 'estudiante' && $idRol !== 2) {
                $this->logIntento($request, 'PORTAL_INCORRECTO', 'login estudiante sin rol 2');
                $this->registrarBitacora(
                    (int) $user->id_usuario,
                    'login_fallido',
                    'Portal incorrecto (estudiante). Rol: ' . $idRol
                );

                return back()->withErrors(['email' => 'Este portal es solo para estudiantes.'])->withInput();
            }

            if ($tipoElegido === 'empleado' && !in_array($idRol, [4, 5], true)) {
                $this->logIntento($request, 'PORTAL_INCORRECTO', 'login empleado sin rol 4/5');
                $this->registrarBitacora(
                    (int) $user->id_usuario,
                    'login_fallido',
                    'Portal incorrecto (empleado). Rol: ' . $idRol
                );

                return back()->withErrors(['email' => 'Este portal es solo para coordinador o secretario.'])->withInput();
            }

            $needs2fa = (int) ($r->needs_2fa ?? 0) === 1;

            if ($needs2fa) {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

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
                    $this->registrarBitacora(
                        (int) $user->id_usuario,
                        '2fa_enviado',
                        'Código 2FA enviado a: ' . $request->email
                    );
                } catch (\Throwable $e) {
                    $this->logIntento($request, '2FA_FALLO_ENVIO', $e->getMessage());
                    $this->registrarBitacora(
                        (int) $user->id_usuario,
                        '2fa_fallido',
                        'Fallo envío 2FA: ' . $e->getMessage()
                    );

                    return back()->withErrors([
                        'email' => 'No se pudo enviar el código 2FA. Intenta más tarde.'
                    ])->withInput();
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
            $this->registrarBitacora((int) $user->id_usuario, 'login_exitoso', 'Inicio de sesión exitoso.');

            return match ($idRol) {
                2 => redirect()->route('panel.estudiante'),
                4 => redirect('/panel-coordinador'),
                5 => redirect('/panel-secretario'),
                default => redirect('/home'),
            };
        } catch (\Exception $e) {
            RateLimiter::hit($key, 300);

            $this->logIntento($request, 'EXCEPTION', $e->getMessage());
            $this->registrarBitacora(
                null,
                'login_fallido',
                'EXCEPTION login: ' . $e->getMessage() . ' | Email: ' . $request->email
            );

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
                $this->registrarBitacora((int) $idUsuario, 'logout', 'Cierre de sesión.');
            }
        } catch (\Throwable $e) {
            //
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal');
    }
}
