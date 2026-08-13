<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Carbon\Carbon;
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

        return view('auth.login', [
            'tipo' => $tipo,
        ]);
    }

    public function loginTipo(Request $request, string $tipo)
    {
        session(['login_tipo' => $tipo]);

        return $this->login($request);
    }

    private function normalizeEmail(Request $request): void
    {
        $email = strtolower(trim((string) $request->input('email')));

        $request->merge([
            'email' => $email,
        ]);
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
        return;
    }

    private function eliminarAutenticacionPorTipo(
        int $idUsuario,
        string $tipo
    ): void {
        DB::select(
            'CALL DEL_LOGIN_AUTHENTICATION_TIPO(?, ?)',
            [
                $idUsuario,
                $tipo,
            ]
        );
    }

    private function insertarAutenticacion(
        int $idUsuario,
        string $tipo,
        string $valorHash,
        string $expiresAt,
        string $accionBitacora,
        string $descripcionBitacora
    ): void {
        DB::select(
            'CALL INS_LOGIN_AUTHENTICATION(?, ?, ?, ?, ?, ?, ?)',
            [
                $idUsuario,
                $tipo,
                $valorHash,
                $expiresAt,
                self::ID_OBJETO_LOGIN,
                $accionBitacora,
                $descripcionBitacora,
            ]
        );
    }

    private function registrarLogoutEvento(
        int $idUsuario,
        string $descripcion
    ): void {
        try {
            DB::select(
                'CALL INS_LOGOUT_EVENTO(?, ?, ?)',
                [
                    $idUsuario,
                    self::ID_OBJETO_LOGIN,
                    $descripcion,
                ]
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function buildAuthUserFromSp(object $r): User
    {
        $user = new User();

        $identifierName = $user->getAuthIdentifierName();

        $user->setAttribute(
            $identifierName,
            (int) $r->id_usuario
        );

        $user->setAttribute(
            'id_usuario',
            (int) $r->id_usuario
        );

        $user->setAttribute(
            'id_persona',
            (int) $r->id_persona
        );

        if (isset($r->id_rol)) {
            $user->setAttribute(
                'id_rol',
                (int) $r->id_rol
            );
        }

        if (isset($r->tipo_usuario)) {
            $user->setAttribute(
                'tipo_usuario',
                $r->tipo_usuario
            );
        }

        if (isset($r->rol)) {
            $user->setAttribute(
                'rol',
                $r->rol
            );
        }

        $user->exists = true;

        return $user;
    }

    private function debeSolicitarTwoFactor(
        $twofaVerifiedAt
    ): bool {
        if (empty($twofaVerifiedAt)) {
            return true;
        }

        try {
            return Carbon::parse($twofaVerifiedAt)
                ->lt(now()->subDays(30));
        } catch (\Throwable $e) {
            report($e);

            return true;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CONTROL DE SESIONES SIMULTÁNEAS
    |--------------------------------------------------------------------------
    */

    private function tieneOtraSesionActiva(
        int $idUsuario,
        string $idSesionActual
    ): bool {
        $minutosVida = (int) config(
            'session.lifetime',
            120
        );

        $limiteActividad = now()
            ->subMinutes($minutosVida)
            ->timestamp;

        return DB::table(
            config('session.table', 'sessions')
        )
            ->where('user_id', $idUsuario)
            ->where('id', '<>', $idSesionActual)
            ->where(
                'last_activity',
                '>=',
                $limiteActividad
            )
            ->exists();
    }

    private function cerrarOtrasSesiones(
        int $idUsuario,
        string $idSesionActual
    ): void {
        DB::table(
            config('session.table', 'sessions')
        )
            ->where('user_id', $idUsuario)
            ->where('id', '<>', $idSesionActual)
            ->delete();
    }

    private function guardarLoginPendiente(
        object $resultado,
        string $tipoElegido,
        string $email
    ): void {
        session([
            'login_pendiente' => [
                'id_usuario' => (int) $resultado->id_usuario,
                'id_persona' => (int) $resultado->id_persona,

                'id_rol' => isset($resultado->id_rol)
                    ? (int) $resultado->id_rol
                    : null,

                'tipo_usuario' =>
                    $resultado->tipo_usuario ?? null,

                'rol' =>
                    $resultado->rol ?? null,

                'twofa_verified_at' =>
                    $resultado->twofa_verified_at ?? null,

                'login_tipo' => $tipoElegido,
                'email' => $email,
            ],
        ]);
    }

    private function enviarCodigoTwoFactor(
        int $idUsuario,
        string $email,
        string $descripcion
    ): bool {
        $code = str_pad(
            (string) random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        $this->eliminarAutenticacionPorTipo(
            $idUsuario,
            'two_factor'
        );

        $this->insertarAutenticacion(
            $idUsuario,
            'two_factor',
            hash('sha256', $code),
            now()->addMinutes(10)->format('Y-m-d H:i:s'),
            '2fa_generado',
            $descripcion
        );

        try {
            Mail::to($email)->send(
                new TwoFactorCodeMail($code)
            );

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
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

            return back()
                ->withErrors([
                    'email' =>
                        'Demasiados intentos fallidos. Espera ' .
                        $this->formatWait($seconds) .
                        ' antes de intentar nuevamente.',
                ])
                ->withInput();
        }

        try {
            $res = DB::select(
                'CALL SEL_LOGIN(?, ?)',
                [
                    $request->email,
                    $request->password,
                ]
            );

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

                return back()
                    ->withErrors([
                        'email' => 'Credenciales inválidas',
                    ])
                    ->withInput();
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

                return back()
                    ->withErrors([
                        'email' => 'Credenciales inválidas',
                    ])
                    ->withInput();
            }

            if (
                !isset($r->pass_hash) ||
                !Hash::check(
                    $request->password,
                    (string) $r->pass_hash
                )
            ) {
                RateLimiter::hit($key, 300);

                $this->registrarIntentoLogin(
                    $request->email,
                    $request->ip(),
                    $request->userAgent(),
                    'CONTRASENA_INCORRECTA',
                    'Hash::check falló',
                    isset($r->id_usuario)
                        ? (int) $r->id_usuario
                        : null,
                    'login_fallido',
                    'Contraseña incorrecta. Email: ' .
                    $request->email .
                    ' | IP: ' .
                    $request->ip()
                );

                return back()
                    ->withErrors([
                        'email' => 'Credenciales inválidas',
                    ])
                    ->withInput();
            }

            RateLimiter::clear($key);

            $tipoElegido = strtolower(
                trim(
                    (string) session('login_tipo')
                )
            );

            $tipoUsuarioDb = strtolower(
                trim(
                    (string) ($r->tipo_usuario ?? '')
                )
            );

            if (
                $tipoElegido === 'estudiante' &&
                $tipoUsuarioDb !== 'estudiante'
            ) {
                $this->registrarIntentoLogin(
                    $request->email,
                    $request->ip(),
                    $request->userAgent(),
                    'PORTAL_INCORRECTO',
                    'Intento en portal estudiante con tipo ' .
                    $tipoUsuarioDb,
                    (int) $r->id_usuario,
                    'login_fallido',
                    'Portal incorrecto (estudiante). Tipo devuelto por SP: ' .
                    $tipoUsuarioDb
                );

                return back()
                    ->withErrors([
                        'email' =>
                            'Este portal es solo para estudiantes.',
                    ])
                    ->withInput();
            }

            if (
                $tipoElegido === 'empleado' &&
                !in_array(
                    $tipoUsuarioDb,
                    [
                        'docente',
                        'coordinador',
                        'secretario',
                        'administrador',
                        'secretaria_general',
                    ],
                    true
                )
            ) {
                $this->registrarIntentoLogin(
                    $request->email,
                    $request->ip(),
                    $request->userAgent(),
                    'PORTAL_INCORRECTO',
                    'Intento en portal empleado con tipo ' .
                    $tipoUsuarioDb,
                    (int) $r->id_usuario,
                    'login_fallido',
                    'Portal incorrecto (empleado). Tipo devuelto por SP: ' .
                    $tipoUsuarioDb
                );

                return back()
                    ->withErrors([
                        'email' =>
                            'Este portal es exclusivo para empleados.',
                    ])
                    ->withInput();
            }

            /*
            |--------------------------------------------------------------------------
            | DETECTAR OTRA SESIÓN ACTIVA
            |--------------------------------------------------------------------------
            */

            if (
                $this->tieneOtraSesionActiva(
                    (int) $r->id_usuario,
                    $request->session()->getId()
                )
            ) {
                $this->guardarLoginPendiente(
                    $r,
                    $tipoElegido,
                    $request->email
                );

                return back()
                    ->withInput(
                        $request->only('email')
                    )
                    ->with(
                        'sesion_duplicada',
                        true
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDAR 2FA
            |--------------------------------------------------------------------------
            */

            $needs2fa = $this->debeSolicitarTwoFactor(
                $r->twofa_verified_at ?? null
            );

            if ($needs2fa) {
                $enviado = $this->enviarCodigoTwoFactor(
                    (int) $r->id_usuario,
                    $request->email,
                    'Código 2FA generado para el usuario.'
                );

                if (!$enviado) {
                    $this->registrarIntentoLogin(
                        $request->email,
                        $request->ip(),
                        $request->userAgent(),
                        '2FA_FALLO_ENVIO',
                        'No se pudo enviar el correo 2FA.',
                        (int) $r->id_usuario,
                        '2fa_fallido',
                        'Fallo en el envío del código 2FA.'
                    );

                    return back()
                        ->withErrors([
                            'email' =>
                                'No se pudo enviar el código 2FA. Intenta más tarde.',
                        ])
                        ->withInput();
                }

                $this->registrarIntentoLogin(
                    $request->email,
                    $request->ip(),
                    $request->userAgent(),
                    '2FA_ENVIADO',
                    'Se envió código 2FA',
                    (int) $r->id_usuario,
                    '2fa_enviado',
                    'Código 2FA enviado a: ' .
                    $request->email
                );

                session([
                    'twofa_user_id' =>
                        (int) $r->id_usuario,

                    'twofa_login_tipo' =>
                        $tipoElegido,
                ]);

                return redirect()
                    ->route('twofa.form')
                    ->with(
                        'status',
                        'Te enviamos un código de 6 dígitos a tu correo.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | INICIO DE SESIÓN NORMAL
            |--------------------------------------------------------------------------
            */

            $authUser = $this->buildAuthUserFromSp($r);

            Auth::login($authUser);

            $request->session()->regenerate();

            session([
                'persona_id' =>
                    $r->id_persona ?? null,

                'rol_texto' =>
                    $r->tipo_usuario ?? null,

                'tipo_usuario' =>
                    $r->tipo_usuario ?? null,

                'login_tipo' =>
                    $tipoElegido,
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

            if ($tipoElegido === 'empleado') {
                return redirect()
                    ->route('empleado.dashboard');
            }

            return redirect()
                ->route('dashboard');
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

            return back()
                ->withErrors([
                    'email' =>
                        'Ocurrió un error al iniciar sesión. Intenta de nuevo.',
                ])
                ->withInput();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR REEMPLAZO DE SESIÓN
    |--------------------------------------------------------------------------
    */

    public function confirmarNuevaSesion(
        Request $request
    ) {
        $pendiente = session('login_pendiente');

        if (
            !is_array($pendiente) ||
            empty($pendiente['id_usuario']) ||
            empty($pendiente['id_persona']) ||
            empty($pendiente['login_tipo']) ||
            empty($pendiente['email'])
        ) {
            session()->forget(
                'login_pendiente'
            );

            return redirect('/portal')
                ->withErrors([
                    'email' =>
                        'La confirmación de la sesión expiró. Inicia sesión nuevamente.',
                ]);
        }

        $idUsuario =
            (int) $pendiente['id_usuario'];

        $tipoElegido =
            (string) $pendiente['login_tipo'];

        $email =
            (string) $pendiente['email'];

        /*
        | Cierra las sesiones anteriores.
        */
        $this->cerrarOtrasSesiones(
            $idUsuario,
            $request->session()->getId()
        );

        $r = (object) [
            'id_usuario' =>
                $idUsuario,

            'id_persona' =>
                (int) $pendiente['id_persona'],

            'id_rol' =>
                $pendiente['id_rol'] ?? null,

            'tipo_usuario' =>
                $pendiente['tipo_usuario'] ?? null,

            'rol' =>
                $pendiente['rol'] ?? null,

            'twofa_verified_at' =>
                $pendiente['twofa_verified_at'] ?? null,
        ];

        session()->forget(
            'login_pendiente'
        );

        /*
        |--------------------------------------------------------------------------
        | CONTINUAR CON 2FA SI CORRESPONDE
        |--------------------------------------------------------------------------
        */

        $needs2fa = $this->debeSolicitarTwoFactor(
            $r->twofa_verified_at ?? null
        );

        if ($needs2fa) {
            $enviado = $this->enviarCodigoTwoFactor(
                $idUsuario,
                $email,
                'Código 2FA generado después de reemplazar una sesión anterior.'
            );

            if (!$enviado) {
                return redirect()
                    ->route(
                        'login.tipo',
                        [
                            'tipo' => $tipoElegido,
                        ]
                    )
                    ->withErrors([
                        'email' =>
                            'La sesión anterior fue cerrada, pero no se pudo enviar el código 2FA. Intenta nuevamente.',
                    ]);
            }

            $this->registrarIntentoLogin(
                $email,
                $request->ip(),
                $request->userAgent(),
                '2FA_ENVIADO',
                'Código 2FA enviado después de cerrar otra sesión',
                $idUsuario,
                '2fa_enviado',
                'Código 2FA enviado después de reemplazar una sesión anterior.'
            );

            session([
                'twofa_user_id' =>
                    $idUsuario,

                'twofa_login_tipo' =>
                    $tipoElegido,
            ]);

            return redirect()
                ->route('twofa.form')
                ->with(
                    'status',
                    'La sesión anterior fue cerrada. Te enviamos un código de 6 dígitos.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | INICIAR LA NUEVA SESIÓN
        |--------------------------------------------------------------------------
        */

        $authUser =
            $this->buildAuthUserFromSp($r);

        Auth::login($authUser);

        $request->session()->regenerate();

        session([
            'persona_id' =>
                $r->id_persona,

            'rol_texto' =>
                $r->tipo_usuario,

            'tipo_usuario' =>
                $r->tipo_usuario,

            'login_tipo' =>
                $tipoElegido,
        ]);

        $this->registrarIntentoLogin(
            $email,
            $request->ip(),
            $request->userAgent(),
            'OK',
            'Inicio de sesión reemplazando una sesión anterior',
            $idUsuario,
            'login_exitoso',
            'Se cerró una sesión anterior y se inició una nueva sesión.'
        );

        if ($tipoElegido === 'empleado') {
            return redirect()
                ->route('empleado.dashboard');
        }

        return redirect()
            ->route('dashboard');
    }

    /*
    |--------------------------------------------------------------------------
    | CANCELAR REEMPLAZO DE SESIÓN
    |--------------------------------------------------------------------------
    */

    public function cancelarNuevaSesion(
        Request $request
    ) {
        $pendiente = session(
            'login_pendiente'
        );

        $tipo = is_array($pendiente)
            ? (
                $pendiente['login_tipo']
                ?? 'estudiante'
            )
            : 'estudiante';

        session()->forget(
            'login_pendiente'
        );

        return redirect()
            ->route(
                'login.tipo',
                [
                    'tipo' => $tipo,
                ]
            )
            ->with(
                'status',
                'Se conservó la sesión iniciada en el otro dispositivo.'
            );
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

        return redirect('/portal');
    }
}
