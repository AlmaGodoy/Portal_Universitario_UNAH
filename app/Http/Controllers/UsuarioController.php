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
            'primer_nombre' => ['required', 'string', 'max:30', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
            'segundo_nombre' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]*$/'],
            'primer_apellido' => ['required', 'string', 'max:30', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
            'segundo_apellido' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]*$/'],
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
        ], [
            'primer_nombre.required' => 'El primer nombre es obligatorio.',
            'primer_nombre.regex' => 'El primer nombre solo debe contener letras y espacios.',
            'segundo_nombre.regex' => 'El segundo nombre solo debe contener letras y espacios.',
            'primer_apellido.required' => 'El primer apellido es obligatorio.',
            'primer_apellido.regex' => 'El primer apellido solo debe contener letras y espacios.',
            'segundo_apellido.regex' => 'El segundo apellido solo debe contener letras y espacios.',
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

        $tipoEmpleado = null;

        if ($tipo === 'estudiante') {
            $request->validate([
                'numero_cuenta' => ['required', 'digits:11'],
                'id_carrera' => 'required|integer',
            ], [
                'numero_cuenta.required' => 'Número de cuenta requerido.',
                'id_carrera.required' => 'Carrera requerida.',
            ]);

            $request->merge([
                'id_rol' => null,
                'id_departamento' => null,
                'cod_empleado' => null,
            ]);
        } else {
            $request->validate([
                'id_rol' => 'required|integer|in:1,4,5',
                'cod_empleado' => 'required|string|max:50',
            ]);

            if ((int) $request->id_rol === 1) {
                $tipoEmpleado = 'secretaria_general';
                $request->merge(['id_departamento' => null]);
            } elseif ((int) $request->id_rol === 4) {
                $tipoEmpleado = 'coordinador';
                $request->validate(['id_departamento' => 'required|integer']);
            } elseif ((int) $request->id_rol === 5) {
                $tipoEmpleado = 'secretario';
                $request->validate(['id_departamento' => 'required|integer']);
            } else {
                return back()->withErrors(['id_rol' => 'Rol de empleado inválido.'])->withInput();
            }

            $request->merge([
                'numero_cuenta' => null,
                'id_carrera' => null,
            ]);
        }

        $nombreCompleto = trim(implode(' ', array_filter([
            trim((string) $request->primer_nombre),
            trim((string) $request->segundo_nombre),
            trim((string) $request->primer_apellido),
            trim((string) $request->segundo_apellido),
        ])));

        try {
            file_put_contents('/tmp/debug_registro.log', 'INICIO ' . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);

            $passwordHash = Hash::make($request->contrasena);
            file_put_contents('/tmp/debug_registro.log', 'HASH OK' . PHP_EOL, FILE_APPEND);

            $res = DB::select('CALL INS_USUARIO(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $nombreCompleto,
                $request->correo,
                $passwordHash,
                $request->tipo_usuario,
                $request->id_rol,
                $request->numero_cuenta,
                $request->id_carrera,
                $request->id_departamento,
                $request->cod_empleado,
                $tipoEmpleado,
            ]);

            file_put_contents('/tmp/debug_registro.log', 'INS_USUARIO OK' . PHP_EOL, FILE_APPEND);

            $row = $res[0] ?? null;
            $resultado = $row->resultado ?? 'ERROR';
            $mensaje = $row->mensaje ?? 'Respuesta inválida del procedimiento';

            file_put_contents(
                '/tmp/debug_registro.log',
                'RESULTADO INS_USUARIO: ' . $resultado . ' | MENSAJE: ' . $mensaje . PHP_EOL,
                FILE_APPEND
            );

            if (strtoupper($resultado) !== 'OK') {
                return back()->withErrors(['registro' => $mensaje])->withInput();
            }

            $idUsuario = $row->id_usuario ?? null;

            file_put_contents(
                '/tmp/debug_registro.log',
                'ID_USUARIO: ' . ($idUsuario ?? 'NULL') . PHP_EOL,
                FILE_APPEND
            );

            if (!$idUsuario) {
                return redirect()->route('portal')
                    ->with('error', 'Usuario creado, pero no se pudo preparar activación por correo.');
            }

            $token = Str::random(64);
            $tokenHash = hash('sha256', $token);

            DB::select('CALL DEL_LOGIN_AUTHENTICATION_TIPO(?, ?)', [
                $idUsuario,
                'email_verification',
            ]);

            file_put_contents('/tmp/debug_registro.log', 'DEL_AUTH OK' . PHP_EOL, FILE_APPEND);

            $resAuth = DB::select('CALL INS_LOGIN_AUTHENTICATION(?, ?, ?, ?, ?, ?, ?)', [
                $idUsuario,
                'email_verification',
                $tokenHash,
                now()->addMinutes(60)->format('Y-m-d H:i:s'),
                self::ID_OBJETO_LOGIN,
                'email_verificacion_generada',
                'Se generó token de verificación para el correo ' . $request->correo,
            ]);

            file_put_contents('/tmp/debug_registro.log', 'INS_AUTH OK' . PHP_EOL, FILE_APPEND);

            $rowAuth = $resAuth[0] ?? null;
            $resultadoAuth = $rowAuth->resultado ?? 'ERROR';
            $mensajeAuth = $rowAuth->mensaje ?? 'No se pudo preparar la verificación por correo.';

            file_put_contents(
                '/tmp/debug_registro.log',
                'RESULTADO INS_AUTH: ' . $resultadoAuth . ' | MENSAJE: ' . $mensajeAuth . PHP_EOL,
                FILE_APPEND
            );

            if (strtoupper($resultadoAuth) !== 'OK') {
                return redirect()->route('portal')
                    ->with('error', 'Usuario creado, pero no se pudo preparar la verificación por correo.');
            }

            $link = route('email.verify', ['token' => $token]);

            file_put_contents(
                '/tmp/debug_registro.log',
                'LINK VERIFICACION: ' . $link . PHP_EOL,
                FILE_APPEND
            );

            try {
                Mail::to($request->correo)->send(new VerifyEmailMail($link));
                file_put_contents('/tmp/debug_registro.log', 'CORREO ENVIADO OK' . PHP_EOL, FILE_APPEND);
            } catch (\Throwable $e) {
                file_put_contents(
                    '/tmp/debug_registro.log',
                    'ERROR CORREO: ' . $e->getMessage() . PHP_EOL,
                    FILE_APPEND
                );

                return redirect()->route('portal')
                    ->with('error', 'Usuario creado, pero no se pudo enviar el correo. Contacta al administrador.');
            }

            session()->forget('register_tipo');

            return redirect()->route('portal')
                ->with('success', 'Usuario creado. Revisa tu correo y activa tu cuenta para poder iniciar sesión.');
        } catch (\Throwable $e) {
            file_put_contents(
                '/tmp/debug_registro.log',
                'ERROR CATCH CREARWEB: ' . $e->getMessage() . ' | Archivo: ' . $e->getFile() . ' | Línea: ' . $e->getLine() . PHP_EOL,
                FILE_APPEND
            );

            return back()->withErrors([
                'registro' => $e->getMessage() . ' | Archivo: ' . $e->getFile() . ' Línea: ' . $e->getLine()
            ])->withInput();
        }
    }

    public function verificarCorreo(string $token)
    {
        try {
            if (trim($token) === '') {
                return redirect()->route('portal')
                    ->with('error', 'Token inválido.');
            }

            $hash = hash('sha256', $token);

            file_put_contents(
                '/tmp/debug_registro.log',
                'VERIFICAR CORREO | TOKEN HASH: ' . $hash . PHP_EOL,
                FILE_APPEND
            );

            $res = DB::select('CALL UPD_VERIFICACION_CORREO_USUARIO(?)', [$hash]);

            $row = $res[0] ?? null;
            $resultado = strtoupper($row->resultado ?? 'ERROR');
            $mensaje = $row->mensaje ?? 'No se pudo verificar la cuenta.';

            file_put_contents(
                '/tmp/debug_registro.log',
                'RESULTADO VERIFICACION: ' . $resultado . ' | MENSAJE: ' . $mensaje . PHP_EOL,
                FILE_APPEND
            );

            if ($resultado !== 'OK') {
                return redirect()->route('portal')->with('error', $mensaje);
            }

            return redirect()->route('portal')->with('success', $mensaje);
        } catch (\Throwable $e) {
            file_put_contents(
                '/tmp/debug_registro.log',
                'ERROR CATCH VERIFICAR: ' . $e->getMessage() . ' | Archivo: ' . $e->getFile() . ' | Línea: ' . $e->getLine() . PHP_EOL,
                FILE_APPEND
            );

            return redirect()->route('portal')
                ->with('error', 'Error al verificar el correo.');
        }
    }
}