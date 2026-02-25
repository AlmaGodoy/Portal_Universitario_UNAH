<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class VerifyEmailController extends Controller
{
    public function verify(string $token)
    {
        $row = DB::table('email_verifications')
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->first();

        if (!$row) {
            return redirect()->route('portal')->with('status', 'Enlace inválido o ya utilizado.');
        }

        if (now()->gt($row->expires_at)) {
            return redirect()->route('portal')->with('status', 'Enlace expirado. Regístrate nuevamente.');
        }

        // ✅ activar cuenta
        DB::table('tbl_usuario')
            ->where('id_usuario', $row->id_usuario)
            ->update(['estado_cuenta' => 1]);

        // ✅ marcar token usado
        DB::table('email_verifications')
            ->where('id_usuario', $row->id_usuario)
            ->update(['used_at' => now(), 'updated_at' => now()]);

        return redirect()->route('portal')->with('status', 'Cuenta activada. Ya puedes iniciar sesión.');
    }
}
