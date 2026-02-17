<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    // ============================
    // ✅ FORMULARIO REGISTRO (WEB)
    // ============================
    public function formRegistro()
    {
        $roles = DB::table('tbl_rol')
            ->select('id_rol', 'nombre_rol')
            ->where('estado_activo', 1)
            ->orderBy('nombre_rol')
            ->get();

        $departamentos = DB::table('tbl_departamento')
            ->select('id_departamento', 'nombre_departamento')
            ->orderBy('nombre_departamento')
            ->get();

        $carreras = DB::table('tbl_carrera')
            ->select('id_carrera', 'id_departamento', 'nombre_carrera')
            ->orderBy('nombre_carrera')
            ->get();

        return view('auth.register_user', compact('roles', 'departamentos', 'carreras'));
    }

    // ============================
    // ✅ CREAR USUARIO (WEB) INS_USUARIO
    // ============================
    public function crearWeb(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'documento' => 'required|string|max:20',
            'correo' => 'required|email|max:100',

            // ✅ CONTRASEÑA FUERTE
            'contrasena' => [
                'required',
                'max:255',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            'tipo_usuario' => 'required|in:estudiante,empleado',
            'id_rol' => 'required|integer',

            'numero_cuenta' => 'nullable|string|max:20',
            'id_carrera' => 'nullable|integer',
            'id_departamento' => 'nullable|integer',
            'cod_empleado' => 'nullable|string|max:50',
            'tipo_empleado' => 'nullable|string|max:50',
        ], [
            // ✅ Mensaje amigable en español
            'contrasena.password' => 'La contraseña debe tener mínimo 8 caracteres e incluir mayúscula, minúscula, número y un carácter especial.',
        ]);

        $tipo = strtolower(trim($request->tipo_usuario));

        if ($tipo === 'estudiante') {
            $request->validate([
                'numero_cuenta' => 'required|string|max:20',
                'id_departamento' => 'required|integer',
                'id_carrera' => 'required|integer',
            ]);

            // limpiar empleado
            $request->merge([
                'cod_empleado' => null,
                'tipo_empleado' => null,
            ]);
        } else { // empleado
            $request->validate([
                'id_departamento' => 'required|integer',
                'cod_empleado' => 'required|string|max:50',
                'tipo_empleado' => 'required|string|max:50',
            ]);

            // limpiar estudiante
            $request->merge([
                'numero_cuenta' => null,
                'id_carrera' => null,
            ]);
        }

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
                $request->id_departamento,
                $request->cod_empleado,
                $request->tipo_empleado
            ]);

            $resultado = $res[0]->resultado ?? 'ERROR';
            $mensaje   = $res[0]->mensaje ?? 'Respuesta inválida del procedimiento';

            if ($resultado !== 'OK') {
                return back()->withErrors(['registro' => $mensaje])->withInput();
            }

            return redirect('/login')->with('status', 'Usuario creado. Ya puedes iniciar sesión.');
        } catch (\Exception $e) {
            return back()->withErrors(['registro' => $e->getMessage()])->withInput();
        }
    }

    // ============================
    // ✅ Crear usuario (API) usando SP INS_USUARIO
    // ============================
    public function crear(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'documento' => 'required|string|max:20',
            'correo' => 'required|email|max:100',

            // ✅ CONTRASEÑA FUERTE
            'contrasena' => [
                'required',
                'max:255',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            'tipo_usuario' => 'required|string|max:50',
            'id_rol' => 'required|integer',

            'numero_cuenta' => 'nullable|string|max:20',
            'id_carrera' => 'nullable|integer',
            'id_departamento' => 'nullable|integer',
            'cod_empleado' => 'nullable|string|max:50',
            'tipo_empleado' => 'nullable|string|max:50',
        ], [
            'contrasena.password' => 'La contraseña debe tener mínimo 8 caracteres e incluir mayúscula, minúscula, número y un carácter especial.',
        ]);

        $tipo = strtolower(trim($request->tipo_usuario));

        if ($tipo === 'estudiante') {
            if (!$request->filled('numero_cuenta')) {
                return response()->json(['resultado' => 'ERROR', 'mensaje' => 'Número de cuenta requerido'], 422);
            }
            if (!$request->filled('id_carrera')) {
                return response()->json(['resultado' => 'ERROR', 'mensaje' => 'id_carrera requerido para estudiante'], 422);
            }
        } else {
            if (!$request->filled('id_departamento')) {
                return response()->json(['resultado' => 'ERROR', 'mensaje' => 'id_departamento requerido para empleado'], 422);
            }
            if (!$request->filled('cod_empleado')) {
                return response()->json(['resultado' => 'ERROR', 'mensaje' => 'cod_empleado requerido para empleado'], 422);
            }
        }

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
                $request->id_departamento,
                $request->cod_empleado,
                $request->tipo_empleado
            ]);

            $resultado = $res[0]->resultado ?? 'ERROR';
            $mensaje   = $res[0]->mensaje ?? 'Respuesta inválida del procedimiento';

            $status = match ($resultado) {
                'OK' => 201,
                'PERSONA_YA_EXISTE' => 409,
                default => 400,
            };

            return response()->json([
                'resultado' => $resultado,
                'mensaje' => $mensaje
            ], $status);
        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function activar($id_persona)
    {
        try {
            $res = DB::select('CALL ACT_USUARIO(?)', [$id_persona]);
            $resultado = $res[0]->resultado ?? 'OK';

            if ($resultado === 'NO_EXISTE') {
                return response()->json(['resultado' => 'NO_EXISTE', 'mensaje' => 'La persona no existe'], 404);
            }

            return response()->json(['resultado' => 'OK', 'mensaje' => 'Usuario activado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function desactivar($id_persona)
    {
        try {
            $res = DB::select('CALL DESACT_USUARIO(?)', [$id_persona]);
            $resultado = $res[0]->resultado ?? 'ERROR';

            if ($resultado === 'NO_EXISTE') {
                return response()->json(['resultado' => 'NO_EXISTE', 'mensaje' => 'La persona no existe'], 404);
            }

            if ($resultado !== 'OK') {
                return response()->json(['resultado' => $resultado, 'mensaje' => 'No se pudo desactivar el usuario'], 400);
            }

            return response()->json(['resultado' => 'OK', 'mensaje' => 'Usuario desactivado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function asignarRol(Request $request, $id_usuario)
    {
        $request->validate([
            'id_rol' => 'required|integer'
        ]);

        try {
            $res = DB::select('CALL INS_ROLES_USUARIOS(?, ?)', [
                (int)$id_usuario,
                (int)$request->id_rol
            ]);

            $resultado = $res[0]->resultado ?? 'ERROR';
            $mensaje   = $res[0]->mensaje ?? 'Respuesta inválida del procedimiento';

            $status = ($resultado === 'OK') ? 200 : 400;

            return response()->json([
                'resultado' => $resultado,
                'mensaje' => $mensaje
            ], $status);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }
}
