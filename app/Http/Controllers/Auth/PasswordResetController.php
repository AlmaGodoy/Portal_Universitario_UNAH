<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    public function showRequestForm()
    {
        return view('recuperar_password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'correo' => 'required|email|max:100',
        ]);

        $correo = strtolower(trim((string)$request->correo));

        $res = DB::select('CALL SOL_REST_CONTRASENA_LOGIN(?)', [$correo]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';

        if ($resultado !== 'OK') {
            $mensaje = $row->mensaje ?? 'No se pudo procesar la solicitud.';
            return back()->withErrors(['correo' => $mensaje])->withInput();
        }

        $idUsuario = $row->id_usuario ?? null;

        if (!$idUsuario) {
            return back()->withErrors([
                'correo' => 'No se pudo identificar al usuario.'
            ])->withInput();
        }

        $tokenPlano = Str::random(64);
        $tokenHash = hash('sha256', $tokenPlano);

        DB::table('tbl_login_autentications')
            ->where('id_usuario', $idUsuario)
            ->where('tipo', 'password_reset')
            ->whereNull('used_at')
            ->delete();

        DB::table('tbl_login_autentications')->insert([
            'id_usuario' => $idUsuario,
            'tipo' => 'password_reset',
            'valor_hash' => $tokenHash,
            'expires_at' => now()->addMinutes(60),
            'used_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $link = route('custom.password.reset.form', ['token' => $tokenPlano]);

        try {
            Mail::to($correo)->send(new ResetPasswordMail($link));
        } catch (\Throwable $e) {
            return back()->withErrors([
                'correo' => 'No se pudo enviar el correo de recuperación.'
            ])->withInput();
        }

        return redirect()->route('portal')
            ->with('status', 'Te enviamos un enlace para restablecer tu contraseña.');
    }

    public function showResetForm(string $token)
    {
        return view('nueva_password', [
            'token' => $token
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $tokenHash = hash('sha256', $request->token);
        $passwordHash = Hash::make($request->password);

        $res = DB::select('CALL UPD_CONTRASENA_LOGIN(?, ?)', [
            $tokenHash,
            $passwordHash
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo actualizar la contraseña.';

        if ($resultado !== 'OK') {
            return back()->withErrors([
                'password' => $mensaje
            ])->withInput();
        }

        return redirect()->route('portal')
            ->with('status', 'Tu contraseña fue restablecida correctamente. Ya puedes iniciar sesión.');
    }
}
