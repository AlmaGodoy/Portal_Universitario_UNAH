<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TwoFactorController extends Controller
{
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

        $record = DB::table('two_factor_codes')
            ->where('id_usuario', $userId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$record) {
            return back()->withErrors([
                'code' => 'Código inválido o expirado.'
            ]);
        }

        if ($record->code_hash !== hash('sha256', $request->code)) {
            return back()->withErrors([
                'code' => 'Código incorrecto.'
            ]);
        }

        // Marcar código como usado
        DB::table('two_factor_codes')
            ->where('id', $record->id)
            ->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);

        // Guardar fecha última verificación 2FA
        DB::table('tbl_usuario')
            ->where('id_usuario', $userId)
            ->update([
                'twofa_verified_at' => now()
            ]);

        $user = User::find($userId);

        Auth::login($user);
        session()->regenerate();

        // limpiar sesión temporal
        session()->forget('twofa_user_id');

        $idRol = $user->id_rol;

        return match ($idRol) {
            2 => redirect('/panel-estudiante'),
            4 => redirect('/panel-coordinador'),
            5 => redirect('/panel-secretario'),
            default => redirect('/home'),
        };
    }
}
