<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TwoFactorController extends Controller
{
    private const ID_OBJETO_LOGIN = 12;

    public function form()
    {
        if (!session('twofa_user_id')) {
            return redirect()->route('portal');
        }

        return view('auth.twofa');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $userId = (int) session('twofa_user_id');

        if (!$userId) {
            return redirect()->route('portal');
        }

        try {
            $res = DB::select('CALL SEL_TWOFA_ACTIVO(?)', [$userId]);

            $row = $res[0] ?? null;
            $resultado = $row->resultado ?? 'ERROR';
            $mensaje = $row->mensaje ?? 'Código inválido o expirado.';

            if ($resultado !== 'OK') {
                return back()->withErrors([
                    'code' => $mensaje
                ]);
            }

            $idAuth = $row->id_auth ?? null;
            $valorHash = $row->valor_hash ?? null;

            if (!$idAuth || !$valorHash) {
                return back()->withErrors([
                    'code' => 'Código inválido o expirado.'
                ]);
            }

            if ($valorHash !== hash('sha256', $request->code)) {
                return back()->withErrors([
                    'code' => 'Código incorrecto.'
                ]);
            }

            $resUpd = DB::select('CALL UPD_TWOFA_VERIFICADO(?, ?, ?)', [
                $idAuth,
                $userId,
                self::ID_OBJETO_LOGIN,
            ]);

            $rowUpd = $resUpd[0] ?? null;
            $resultadoUpd = $rowUpd->resultado ?? 'ERROR';
            $mensajeUpd = $rowUpd->mensaje ?? 'No se pudo verificar el código 2FA.';

            if ($resultadoUpd !== 'OK') {
                return back()->withErrors([
                    'code' => $mensajeUpd
                ]);
            }

            $user = User::find($userId);

            if (!$user) {
                return redirect()->route('portal')
                    ->with('status', 'No se pudo completar el inicio de sesión.');
            }

            Auth::login($user);
            session()->regenerate();

            session()->forget('twofa_user_id');
            session()->forget('twofa_login_tipo');

            $idRol = (int) $user->id_rol;

            return match ($idRol) {
                2 => redirect()->route('dashboard'),
                4 => redirect('dashboard'),
                5 => redirect('dashboard'),
                default => redirect('dashboard'),
            };

        } catch (\Throwable $e) {
            return back()->withErrors([
                'code' => 'Ocurrió un error al verificar el código. Intenta nuevamente.'
            ]);
        }
    }
}
