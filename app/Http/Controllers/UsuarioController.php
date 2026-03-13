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

        $correo = strtolower(trim((string)$request->correo));
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

        $tipo = strtolower(trim((string)$request->tipo_usuario));

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
                'id_departamento' => null,
                'cod_empleado' => null,
                'tipo_empleado' => null,
            ]);
        } else {
            $request->validate([
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

            // Nuevo SP: ya no recibe id_rol
            $res = DB::select('CALL INS_USUARIO(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->nombre,
                $request->correo,
                $passwordHash,
                $request->tipo_usuario,
                $request->numero_cuenta,
                $request->id_carrera,
                $request->id_departamento,
                $request->cod_empleado,
                $request->tipo_empleado
            ]);

            $row = $res[0] ?? null;
            $resultado = $row->resultado ?? 'ERROR';
            $mensaje   = $row->mensaje ?? 'Respuesta inválida del procedimiento';

            if ($resultado !== 'OK') {
                return back()->withErrors(['registro' => $mensaje])->withInput();
            }

            $idUsuario = $row->id_usuario ?? null;

            if (!$idUsuario) {
                return redirect()->route('portal')
                    ->with('status', 'Usuario creado, pero no se pudo preparar activación por correo.');
            }

            $token = Str::random(64);

            DB::table('tbl_login_autentications')
                ->where('id_usuario', $idUsuario)
                ->where('tipo', 'email_verification')
                ->delete();

            DB::table('tbl_login_autentications')->insert([
                'id_usuario' => $idUsuario,
                'tipo' => 'email_verification',
                'valor_hash' => hash('sha256', $token),
                'expires_at' => now()->addMinutes(60),
                'used_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $link = route('email.verify', ['token' => $token]);

            try {
                Mail::to($request->correo)->send(new VerifyEmailMail($link));

                $this->registrarBitacora(
                    (int)$idUsuario,
                    'email_verificacion_enviada',
                    'Se envió correo de verificación a ' . $request->correo
                );
            } catch (\Throwable $e) {
                $this->registrarBitacora(
                    (int)$idUsuario,
                    'email_verificacion_fallida',
                    'Fallo envío a ' . $request->correo . ' | ' . $e->getMessage()
                );

                return redirect()->route('portal')
                    ->with('status', 'Usuario creado, pero NO se pudo enviar el correo. Contacta al administrador.');
            }

            session()->forget('register_tipo');

            return redirect()->route('portal')
                ->with('status', 'Usuario creado. Revisa tu correo y activa tu cuenta para poder iniciar sesión.');
        } catch (\Exception $e) {
            return back()->withErrors(['registro' => $e->getMessage()])->withInput();
        }
    }
}
