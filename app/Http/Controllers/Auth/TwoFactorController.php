<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TwoFactorController extends Controller
{
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
        }
    }

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

        $userId = session('twofa_user_id');

        if (!$userId) {
            return redirect()->route('portal');
        }

        $record = DB::table('tbl_login_autentications')
            ->where('id_usuario', $userId)
            ->where('tipo', 'two_factor')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id_auth')
            ->first();

        if (!$record) {
            return back()->withErrors([
                'code' => 'Código inválido o expirado.'
            ]);
        }

        if ($record->valor_hash !== hash('sha256', $request->code)) {
            return back()->withErrors([
                'code' => 'Código incorrecto.'
            ]);
        }

        DB::table('tbl_login_autentications')
            ->where('id_auth', $record->id_auth)
            ->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('tbl_usuario')
            ->where('id_usuario', $userId)
            ->update([
                'twofa_verified_at' => now()
            ]);

        $this->registrarBitacora(
            (int)$userId,
            '2fa_verificado',
            'Código 2FA verificado correctamente.'
        );

        $user = User::find($userId);

        Auth::login($user);
        session()->regenerate();

        session()->forget('twofa_user_id');
        session()->forget('twofa_login_tipo');

        $idRol = $user->id_rol;

        return match ($idRol) {
            2 => redirect('/panel-estudiante'),
            4 => redirect('/panel-coordinador'),
            5 => redirect('/panel-secretario'),
            default => redirect('/home'),
        };
    }
}
