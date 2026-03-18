<?php

namespace App\Http\Controllers;

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
    private const ID_OBJETO_LOGIN = 12;

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

    private function registrarIntentoLogin(
        string $email,
        string $ip,
        ?string $userAgent,
        string $resultado,
        ?string $detalle = null,
        ?int $idUsuario = null,
        ?string $accionBitacora = null,
        ?string $descripcionBitacora = null
    ): void {
        try {
            DB::select('CALL INS_LOGIN_INTENTO(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $email,
                $ip,
                $userAgent ? substr($userAgent, 0, 255) : null,
                $resultado,
                $detalle,
                $idUsuario,
                self::ID_OBJETO_LOGIN,
                $accionBitacora,
                $descripcionBitacora,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function eliminarAutenticacionPorTipo(int $idUsuario, string $tipo): void
    {
        DB::select('CALL DEL_LOGIN_AUTHENTICATION_TIPO(?, ?)', [
            $idUsuario,
            $tipo,
        ]);
    }

    private function insertarAutenticacion(
        int $idUsuario,
        string $tipo,
        string $valorHash,
        string $expiresAt,
        string $accionBitacora,
        string $descripcionBitacora
    ): void {
        DB::select('CALL INS_LOGIN_AUTHENTICATION(?, ?, ?, ?, ?, ?, ?)', [
            $idUsuario,
            $tipo,
            $valorHash,
            $expiresAt,
            self::ID_OBJETO_LOGIN,
            $accionBitacora,
            $descripcionBitacora,
        ]);
    }

    private function registrarLogoutEvento(int $idUsuario, string $descripcion): void
    {
        try {
            DB::select('CALL INS_LOGOUT_EVENTO(?, ?, ?)', [
                $idUsuario,
                self::ID_OBJETO_LOGIN,
                $descripcion,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function buildAuthUserFromSp(object $r): User
    {
        $user = new User();

        $identifierName = $user->getAuthIdentifierName();
        $user->setAttribute($identifierName, (int) $r->id_usuario);
        $user->setAttribute('id_usuario', (int) $r->id_usuario);
        $user->setAttribute('id_persona', (int) $r->id_persona);
        $user->exists = true;

        return $user;
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

            $this->registrarIntentoLogin(
                $request->email,
                $request->ip(),
                $request->userAgent(),
                'BLOQUEADO_THROTTLE',
                "Esperar {$seconds}s"
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

                $this->registrarIntentoLogin(
                    $request->email,
                    $request->ip(),
                    $request->userAgent(),
                    'CREDENCIALES_INVALIDAS',
                    'Respuesta inválida SP'
                );

                return back()->withErrors([
                    'email' => 'Credenciales inválidas'
                ])->withInput();
            }

            if ($r->resultado !== 'OK') {
                RateLimiter::hit($key, 300);

                $this->registrarIntentoLogin(
                    $request->email,
                    $request->ip(),
                    $request->userAgent(),
                    'SP_RECHAZO',
                    (string) $r->resultado
                );

                return back()->withErrors([
                    'email' => 'Credenciales inválidas'
                ])->withInput();
            }

            if (!isset($r->pass_hash) || !Hash::check($request->password, (string) $r->pass_hash)) {
                RateLimiter::hit($key, 300);

                $this->registrarIntentoLogin(
                    $request->email,
                    $request->ip(),
                    $request->userAgent(),
                    'CONTRASENA_INCORRECTA',
                    'Hash::check falló',
                    isset($r->id_usuario) ? (int) $r->id_usuario : null,
                    'login_fallido',
                    'Contraseña incorrecta. Email: ' . $request->email . ' | IP: ' . $request->ip()
                );

                return back()->withErrors([
                    'email' => 'Credenciales inválidas'
                ])->withInput();
            }

            RateLimiter::clear($key);

            $tipoElegido = strtolower(trim((string) session('login_tipo')));
            $tipoUsuarioDb = strtolower(trim((string) ($r->tipo_usuario ?? '')));
            $rolNombre = strtolower(trim((string) ($r->rol ?? '')));

            if ($tipoElegido === 'estudiante' && $tipoUsuarioDb !== 'estudiante') {
                $this->registrarIntentoLogin(
                    $request->email,
                    $request->ip(),
                    $request->userAgent(),
                    'PORTAL_INCORRECTO',
                    'Intento en portal estudiante con tipo ' . $tipoUsuarioDb,
                    (int) $r->id_usuario,
                    'login_fallido',
                    'Portal incorrecto (estudiante). Tipo devuelto por SP: ' . $tipoUsuarioDb
                );

                return back()->withErrors([
                    'email' => 'Este portal es solo para estudiantes.'
                ])->withInput();
            }

            if (
                $tipoElegido === 'empleado'
                && (
                    $tipoUsuarioDb !== 'empleado'
                    || !in_array($rolNombre, ['coordinador', 'secretario'], true)
                )
            ) {
                $this->registrarIntentoLogin(
                    $request->email,
                    $request->ip(),
                    $request->userAgent(),
                    'PORTAL_INCORRECTO',
                    'Intento en portal empleado con rol ' . $rolNombre,
                    (int) $r->id_usuario,
                    'login_fallido',
                    'Portal incorrecto (empleado). Rol devuelto por SP: ' . $rolNombre
                );

                return back()->withErrors([
                    'email' => 'Este portal es solo para coordinador o secretario.'
                ])->withInput();
            }

            $needs2fa = (int) ($r->needs_2fa ?? 0) === 1;

            if ($needs2fa) {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                $this->eliminarAutenticacionPorTipo((int) $r->id_usuario, 'two_factor');

                $this->insertarAutenticacion(
                    (int) $r->id_usuario,
                    'two_factor',
                    hash('sha256', $code),
                    now()->addMinutes(10)->format('Y-m-d H:i:s'),
                    '2fa_generado',
                    'Código 2FA generado para el usuario.'
                );

                try {
                    Mail::to($request->email)->send(new TwoFactorCodeMail($code));

                    $this->registrarIntentoLogin(
                        $request->email,
                        $request->ip(),
                        $request->userAgent(),
                        '2FA_ENVIADO',
                        'Se envió código 2FA',
                        (int) $r->id_usuario,
                        '2fa_enviado',
                        'Código 2FA enviado a: ' . $request->email
                    );
                } catch (\Throwable $e) {
                    report($e);

                    $this->registrarIntentoLogin(
                        $request->email,
                        $request->ip(),
                        $request->userAgent(),
                        '2FA_FALLO_ENVIO',
                        $e->getMessage(),
                        (int) $r->id_usuario,
                        '2fa_fallido',
                        'Fallo envío 2FA: ' . $e->getMessage()
                    );

                    return back()->withErrors([
                        'email' => 'No se pudo enviar el código 2FA. Intenta más tarde.'
                    ])->withInput();
                }

                session([
                    'twofa_user_id' => (int) $r->id_usuario,
                    'twofa_login_tipo' => $tipoElegido,
                ]);

                return redirect()->route('twofa.form')
                    ->with('status', 'Te enviamos un código de 6 dígitos a tu correo.');
            }

            $authUser = $this->buildAuthUserFromSp($r);

            Auth::login($authUser);
            $request->session()->regenerate();

            session([
                'persona_id' => $r->id_persona ?? null,
                'rol_texto' => $r->rol ?? null,
                'tipo_usuario' => $r->tipo_usuario ?? null,
            ]);

            $this->registrarIntentoLogin(
                $request->email,
                $request->ip(),
                $request->userAgent(),
                'OK',
                'Login exitoso',
                (int) $r->id_usuario,
                'login_exitoso',
                'Inicio de sesión exitoso.'
            );

            if ($tipoUsuarioDb === 'estudiante') {
                return redirect()->route('panel.estudiante');
            }

            return match ($rolNombre) {
                'coordinador' => redirect('/panel-coordinador'),
                'secretario' => redirect('/panel-secretario'),
                default => redirect('/home'),
            };
        } catch (\Throwable $e) {
            report($e);

            RateLimiter::hit($key, 300);

            $this->registrarIntentoLogin(
                $request->email,
                $request->ip(),
                $request->userAgent(),
                'EXCEPTION',
                $e->getMessage()
            );

            return back()->withErrors([
                'email' => 'Ocurrió un error al iniciar sesión. Intenta de nuevo.'
            ])->withInput();
        }
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $this->registrarLogoutEvento(
                (int) Auth::id(),
                'Cierre de sesión.'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal');
    }
}
