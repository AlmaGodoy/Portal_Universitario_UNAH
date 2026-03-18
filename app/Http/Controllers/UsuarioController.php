<?php

namespace App\Http\Controllers;

use App\Mail\VerifyEmailMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    private const ID_OBJETO_LOGIN = 12;

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

    public function formRegistroTipo(string $tipo)
    {
        session(['register_tipo' => $tipo]);
        return $this->formRegistro();
    }

    public function crearWeb(Request $request)
    {
        $tipoFijo = session('register_tipo');
        if ($tipoFijo) {
            $request->merge(['tipo_usuario' => $tipoFijo]);
        }

        $correo = strtolower(trim((string) $request->correo));
        $request->merge(['correo' => $correo]);

        $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
            'correo' => ['required', 'email', 'max:100'],
            'contrasena' => [
                'required',
                'max:255',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
            'tipo_usuario' => 'required|in:estudiante,empleado',
            'id_rol' => 'nullable|integer',
            'numero_cuenta' => ['nullable', 'digits:11'],
            'id_carrera' => 'nullable|integer',
            'id_departamento' => 'nullable|integer',
            'cod_empleado' => 'nullable|string|max:50',
            'tipo_empleado' => 'nullable|string|max:50',
        ], [
            'nombre.regex' => 'El nombre solo debe contener letras y espacios.',
            'numero_cuenta.digits' => 'El número de cuenta debe tener exactamente 11 números.',
            'contrasena.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $tipo = strtolower(trim((string) $request->tipo_usuario));

        if ($tipo === 'estudiante' && !str_ends_with($correo, '@unah.hn')) {
            return back()->withErrors([
                'correo' => 'Estudiante: el correo debe terminar en @unah.hn'
            ])->withInput();
        }

        if ($tipo === 'empleado' && !str_ends_with($correo, '@unah.edu.hn')) {
            return back()->withErrors([
                'correo' => 'Empleado: el correo debe terminar en @unah.edu.hn'
            ])->withInput();
        }

        if ($tipo === 'estudiante') {
            $request->validate([
                'numero_cuenta' => ['required', 'digits:11'],
                'id_carrera' => 'required|integer',
            ], [
                'numero_cuenta.required' => 'Número de cuenta requerido.',
                'id_carrera.required' => 'Carrera requerida.',
            ]);

            $request->merge([
                'id_rol' => null, // el SP busca el rol estudiante dinámicamente
                'id_departamento' => null,
                'cod_empleado' => null,
                'tipo_empleado' => null,
            ]);
        } else {
            $request->validate([
                'id_rol' => 'required|integer|in:4,5',
                'id_departamento' => 'required|integer',
                'cod_empleado' => 'required|string|max:50',
                'tipo_empleado' => 'required|string|in:coordinador,secretario|max:50',
            ], [
                'tipo_empleado.in' => 'El tipo de empleado debe ser coordinador o secretario.'
            ]);

            $request->merge([
                'numero_cuenta' => null,
                'id_carrera' => null,
            ]);
        }

        try {
            $passwordHash = Hash::make($request->contrasena);

            $res = DB::select('CALL INS_USUARIO(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->nombre,
                $request->correo,
                $passwordHash,
                $request->tipo_usuario,
                $request->id_rol,
                $request->numero_cuenta,
                $request->id_carrera,
                $request->id_departamento,
                $request->cod_empleado,
                $request->tipo_empleado,
            ]);

            $row = $res[0] ?? null;
            $resultado = $row->resultado ?? 'ERROR';
            $mensaje = $row->mensaje ?? 'Respuesta inválida del procedimiento';

            if ($resultado !== 'OK') {
                return back()->withErrors(['registro' => $mensaje])->withInput();
            }

            $idUsuario = $row->id_usuario ?? null;

            if (!$idUsuario) {
                return redirect()->route('portal')
                    ->with('status', 'Usuario creado, pero no se pudo preparar activación por correo.');
            }

            $token = Str::random(64);
            $tokenHash = hash('sha256', $token);

            DB::select('CALL DEL_LOGIN_AUTHENTICATION_TIPO(?, ?)', [
                $idUsuario,
                'email_verification',
            ]);

            $resAuth = DB::select('CALL INS_LOGIN_AUTHENTICATION(?, ?, ?, ?, ?, ?, ?)', [
                $idUsuario,
                'email_verification',
                $tokenHash,
                now()->addMinutes(60)->format('Y-m-d H:i:s'),
                self::ID_OBJETO_LOGIN,
                'email_verificacion_generada',
                'Se generó token de verificación para el correo ' . $request->correo,
            ]);

            $rowAuth = $resAuth[0] ?? null;
            $resultadoAuth = $rowAuth->resultado ?? 'ERROR';
            $mensajeAuth = $rowAuth->mensaje ?? 'No se pudo registrar el token de verificación.';

            if ($resultadoAuth !== 'OK') {
                return redirect()->route('portal')
                    ->with('status', 'Usuario creado, pero no se pudo preparar la verificación por correo.');
            }

            $link = route('email.verify', ['token' => $token]);

            try {
                Mail::to($request->correo)->send(new VerifyEmailMail($link));
            } catch (\Throwable $e) {
                return redirect()->route('portal')
                    ->with('status', 'Usuario creado, pero NO se pudo enviar el correo. Contacta al administrador.');
            }

            session()->forget('register_tipo');

            return redirect()->route('portal')
                ->with('status', 'Usuario creado. Revisa tu correo y activa tu cuenta para poder iniciar sesión.');

        } catch (\Throwable $e) {
            return back()->withErrors([
                'registro' => $e->getMessage()
            ])->withInput();
        }
    }

    public function verificarCorreo(string $token)
    {
        try {
            $hash = hash('sha256', $token);

            $res = DB::select('CALL UPD_VERIFICACION_CORREO_USUARIO(?)', [$hash]);

            $row = $res[0] ?? null;
            $resultado = $row->resultado ?? 'ERROR';
            $mensaje = $row->mensaje ?? 'No se pudo verificar la cuenta.';

            return redirect()->route('portal')->with('status', $mensaje);

        } catch (\Throwable $e) {
            return redirect()->route('portal')
                ->with('status', 'No se pudo verificar la cuenta. Intenta nuevamente.');
        }
    }
}
