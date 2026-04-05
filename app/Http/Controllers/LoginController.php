<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    private const ID_OBJETO_LOGIN = 12;
    private const TRUSTED_DEVICE_COOKIE = 'trusted_device_token';

    public function showLoginFormTipo(string $tipo)
    {
        return view('auth.login', ['tipo' => $tipo]);
    }

    public function loginTipo(Request $request, string $tipo)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $email = strtolower(trim((string) $request->email));

            $res = DB::select('CALL SEL_LOGIN(?, ?)', [$email, '']);
            $r = $res[0] ?? null;

            if (!$r || $r->resultado !== 'OK' || !Hash::check($request->password, (string) $r->pass_hash)) {
                return back()->withErrors([
                    'email' => 'Credenciales inválidas.'
                ])->withInput();
            }

            $idUsuario = (int) ($r->id_usuario ?? 0);

            if (!$idUsuario) {
                return back()->withErrors([
                    'email' => 'No se pudo identificar el usuario.'
                ])->withInput();
            }

            /*
            |--------------------------------------------------------------------------
            | 1) Verificar si este dispositivo sigue confiable por 30 días
            |--------------------------------------------------------------------------
            */
            $trustedToken = Cookie::get(self::TRUSTED_DEVICE_COOKIE);

            if ($trustedToken) {
                $trustedHash = hash('sha256', $trustedToken);

                $trusted = DB::table('tbl_login_autentications')
                    ->where('id_usuario', $idUsuario)
                    ->where('tipo', 'trusted_device')
                    ->where('valor_hash', $trustedHash)
                    ->whereNull('used_at')
                    ->where('expires_at', '>', now())
                    ->orderByDesc('id_auth')
                    ->first();

                if ($trusted) {
                    $user = User::find($idUsuario) ?? new User();

                    if (!$user->exists) {
                        $user->setAttribute('id_usuario', $idUsuario);
                        $user->exists = true;
                    }

                    Auth::login($user);
                    $request->session()->regenerate();

                    session([
                        'rol_texto' => strtolower(trim((string) ($r->rol ?? ''))),
                        'tipo_usuario' => strtolower(trim((string) ($r->tipo_usuario ?? ''))),
                    ]);

                    if (session('tipo_usuario') === 'estudiante') {
                        return redirect()->route('dashboard');
                    }

                    return redirect()->route('empleado.dashboard');
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 2) Si no es un dispositivo confiable, generar código 2FA
            |--------------------------------------------------------------------------
            */
            $code = (string) random_int(100000, 999999);
            $codeHash = hash('sha256', $code);

            DB::select('CALL DEL_LOGIN_AUTHENTICATION_TIPO(?, ?)', [
                $idUsuario,
                'two_factor',
            ]);

            $res2fa = DB::select('CALL INS_LOGIN_AUTHENTICATION(?, ?, ?, ?, ?, ?, ?)', [
                $idUsuario,
                'two_factor',
                $codeHash,
                now()->addMinutes(10)->format('Y-m-d H:i:s'),
                self::ID_OBJETO_LOGIN,
                'codigo_2fa_generado',
                'Se generó código 2FA para el correo ' . $email,
            ]);

            $row2fa = $res2fa[0] ?? null;
            $resultado2fa = $row2fa->resultado ?? 'ERROR';
            $mensaje2fa = $row2fa->mensaje ?? 'No se pudo generar el código 2FA.';

            if ($resultado2fa !== 'OK') {
                return back()->withErrors([
                    'email' => $mensaje2fa
                ])->withInput();
            }

            try {
                Mail::to($email)->send(new TwoFactorCodeMail($code));
            } catch (\Throwable $e) {
                return back()->withErrors([
                    'email' => 'No se pudo enviar el código de verificación al correo.'
                ])->withInput();
            }

            session([
                'twofa_user_id' => $idUsuario,
                'twofa_login_tipo' => strtolower(trim((string) ($r->tipo_usuario ?? $tipo))),
                'rol_texto' => strtolower(trim((string) ($r->rol ?? ''))),
                'tipo_usuario' => strtolower(trim((string) ($r->tipo_usuario ?? ''))),
            ]);

            return redirect()->route('twofa.form');

        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'email' => 'Error de servidor.'
            ])->withInput();
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
