<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function formRegistro()
    {
        $roles = DB::table('tbl_rol')
            ->select('id_rol', 'nombre_rol')
            ->where('estado_activo', 1)
            ->orderBy('nombre_rol')
            ->get();

        // 👇 Solo lo seguimos cargando porque EMPLEADO sí lo usa
        $departamentos = DB::table('tbl_departamento')
            ->select('id_departamento', 'nombre_departamento')
            ->orderBy('nombre_departamento')
            ->get();

        // 👇 CARRERAS completas (ya no dependemos de departamento para estudiante)
        $carreras = DB::table('tbl_carrera')
            ->select('id_carrera', 'id_departamento', 'nombre_carrera')
            ->orderBy('nombre_carrera')
            ->get();

        return view('auth.register_user', compact('roles', 'departamentos', 'carreras'));
    }

    public function formRegistroTipo(string $tipo)
    {
        session(['register_tipo' => $tipo]); // estudiante|empleado
        return $this->formRegistro();
    }

    public function crearWeb(Request $request)
    {
        // Si viene desde /register/{tipo}, forza tipo_usuario desde sesión
        $tipoFijo = session('register_tipo');
        if ($tipoFijo) {
            $request->merge(['tipo_usuario' => $tipoFijo]);
        }

        // ✅ Validación base
        $request->validate([
            'nombre' => 'required|string|max:100',
            'documento' => ['required','digits:13'],
            'correo' => 'required|email|max:100',

            'contrasena' => [
                'required',
                'max:255',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],

            'tipo_usuario' => 'required|in:estudiante,empleado',
            'id_rol' => 'required|integer',

            'numero_cuenta' => ['nullable','digits:11'],
            'id_carrera' => 'nullable|integer',

            // 👇 para empleado sigue existiendo, para estudiante la mandamos null
            'id_departamento' => 'nullable|integer',

            'cod_empleado' => 'nullable|string|max:50',
            'tipo_empleado' => 'nullable|string|max:50',
        ], [
            'documento.digits' => 'El documento debe tener exactamente 13 números.',
            'numero_cuenta.digits' => 'El número de cuenta debe tener exactamente 11 números.',
            'contrasena.password' => 'La contraseña debe tener mayúscula, minúscula, número y símbolo.',
        ]);

        $tipo = strtolower(trim($request->tipo_usuario));

        // =========================
        // ✅ ESTUDIANTE (sin depto)
        // =========================
        if ($tipo === 'estudiante') {

            // rol fijo estudiante
            $request->merge(['id_rol' => 2]);

            // ✅ ahora solo pedimos carrera (y numero_cuenta)
            $request->validate([
                'numero_cuenta' => ['required','digits:11'],
                'id_carrera' => 'required|integer',
            ], [
                'numero_cuenta.digits' => 'El número de cuenta debe tener exactamente 11 números.',
            ]);

            // ✅ estudiante NO usa datos de empleado ni departamento
            $request->merge([
                'id_departamento' => null,
                'cod_empleado' => null,
                'tipo_empleado' => null,
            ]);

        } else {

            // =========================
            // ✅ EMPLEADO
            // =========================

            // empleado: SOLO coord/secretario
            $request->validate([
                'id_rol' => 'required|integer|in:4,5',
                'id_departamento' => 'required|integer',
                'cod_empleado' => 'required|string|max:50',
                'tipo_empleado' => 'required|string|max:50',
            ]);

            // empleado NO usa datos de estudiante
            $request->merge([
                'numero_cuenta' => null,
                'id_carrera' => null,
            ]);
        }

        // =========================
        // ✅ LLAMADA SP
        // =========================
        try {
            $res = DB::select('CALL INS_USUARIO(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->nombre,
                $request->documento,
                $request->correo,
                $request->contrasena,
                $request->tipo_usuario,
                $request->id_rol,
                $request->numero_cuenta,
                $request->id_carrera,
                $request->id_departamento, // <- null si estudiante
                $request->cod_empleado,
                $request->tipo_empleado
            ]);

            $resultado = $res[0]->resultado ?? 'ERROR';
            $mensaje   = $res[0]->mensaje ?? 'Respuesta inválida del procedimiento';

            if ($resultado !== 'OK') {
                return back()->withErrors(['registro' => $mensaje])->withInput();
            }

            session()->forget('register_tipo');

            return redirect()->route('portal')->with('status', 'Usuario creado correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['registro' => $e->getMessage()])->withInput();
        }
    }
}
