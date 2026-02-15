<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User; // ✅ AGREGAR
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {

            $res = DB::select('CALL SEL_LOGIN(?, ?)', [
                $request->email,
                $request->password
            ]);

            if (empty($res) || !isset($res[0]->resultado)) {
                return back()->withErrors([
                    'email' => 'Credenciales inválidas'
                ]);
            }

            $r = $res[0];

            if ($r->resultado !== 'OK') {
                return back()->withErrors([
                    'email' => $r->resultado
                ]);
            }

            // ✅ LOGIN REAL CON AUTH (NO session manual)
            $user = User::find($r->id_usuario);

            if (!$user) {
                return back()->withErrors([
                    'email' => 'Usuario no encontrado en tbl_usuario'
                ]);
            }

            Auth::login($user);
            $request->session()->regenerate();

            // ✅ Si quieres seguir usando estos datos, GUÁRDALOS DESPUÉS del Auth::login
            session([
                'persona_id' => $r->id_persona,
                'rol' => $r->rol,
                'tipo_usuario' => $r->tipo_usuario
            ]);

            return redirect('/home');

        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => $e->getMessage()
            ]);
        }
    }
}
