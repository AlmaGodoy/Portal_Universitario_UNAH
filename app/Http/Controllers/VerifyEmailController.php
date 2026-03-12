<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class VerifyEmailController extends Controller
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

    public function verify(string $token)
    {
        $row = DB::table('tbl_login_autentications')
            ->where('tipo', 'email_verification')
            ->where('valor_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->first();

        if (!$row) {
            return redirect()->route('portal')->with('status', 'Enlace inválido o ya utilizado.');
        }

        if (now()->gt($row->expires_at)) {
            return redirect()->route('portal')->with('status', 'Enlace expirado. Regístrate nuevamente.');
        }

        DB::table('tbl_usuario')
            ->where('id_usuario', $row->id_usuario)
            ->update(['estado_cuenta' => 1]);

        DB::table('tbl_login_autentications')
            ->where('id_auth', $row->id_auth)
            ->update([
                'used_at' => now(),
                'updated_at' => now()
            ]);

        $this->registrarBitacora(
            (int)$row->id_usuario,
            'cuenta_activada',
            'Cuenta activada correctamente mediante verificación de correo.'
        );

        return redirect()->route('portal')->with('status', 'Cuenta activada. Ya puedes iniciar sesión.');
    }
}
