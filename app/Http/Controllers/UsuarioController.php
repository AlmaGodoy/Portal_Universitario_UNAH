<?php

namespace App\Http\Controllers;

use App\Helpers\Bitacora;
use App\Mail\VerifyEmailMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
        session(['register_tipo' => $tipo]); // estudiante|empleado
        return $this->formRegistro();
    }

    public function crearWeb(Request $request)
    {
        // Si viene desde /register/{tipo}, fuerza tipo_usuario desde sesión
        $tipoFijo = session('register_tipo');
        if ($tipoFijo) {
            $request->merge(['tipo_usuario' => $tipoFijo]);
        }

        // Convertir correo a minúsculas automáticamente
$correo = strtolower(trim((string)$request->correo));

$request->merge([
    'correo' => $correo
]);
        // ✅ Validación base (YA NO HAY documento)
        $request->validate([
            'nombre' => ['required','string','max:100','regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
            'correo' => ['required','email','max:100'],

            'contrasena' => [
                'required',
                'max:255',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],

            'tipo_usuario' => 'required|in:estudiante,empleado',
            'id_rol' => 'nullable|integer',

            'numero_cuenta' => ['nullable','digits:11'],
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

        // ✅ Validación fuerte del dominio de correo
        $correo = strtolower(trim((string)$request->correo));
        if ($tipo === 'estudiante' && !str_ends_with($correo, '@unah.hn')) {
            return back()->withErrors(['correo' => 'Estudiante: el correo debe terminar en @unah.hn'])->withInput();
        }
        if ($tipo === 'empleado' && !str_ends_with($correo, '@unah.edu.hn')) {
            return back()->withErrors(['correo' => 'Empleado: el correo debe terminar en @unah.edu.hn'])->withInput();
        }

        // =========================
        // ✅ ESTUDIANTE
        // =========================
        if ($tipo === 'estudiante') {

            // rol fijo estudiante
            $request->merge(['id_rol' => 2]);

            $request->validate([
                'numero_cuenta' => ['required','digits:11'],
                'id_carrera' => 'required|integer',
            ], [
                'numero_cuenta.required' => 'Número de cuenta requerido.',
                'id_carrera.required' => 'Carrera requerida.',
            ]);

            // estudiante NO usa empleado/departamento
            $request->merge([
                'id_departamento' => null,
                'cod_empleado' => null,
                'tipo_empleado' => null,
            ]);

        } else {

            // =========================
            // ✅ EMPLEADO
            // =========================
            $request->validate([
                'id_rol' => 'required|integer|in:4,5',
                'id_departamento' => 'required|integer',
                'cod_empleado' => 'required|string|max:50',
                'tipo_empleado' => 'required|string|max:50',
            ]);

            // empleado NO usa estudiante
            $request->merge([
                'numero_cuenta' => null,
                'id_carrera' => null,
            ]);
        }

        // =========================
        // ✅ SP + Email verification + Bitácora
        // =========================
        try {
            $passwordHash = Hash::make($request->contrasena);

            // ✅ YA NO SE ENVÍA documento => 10 parámetros
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
                $request->tipo_empleado
            ]);

            $resultado = $res[0]->resultado ?? 'ERROR';
            $mensaje   = $res[0]->mensaje ?? 'Respuesta inválida del procedimiento';

            if ($resultado !== 'OK') {
                return back()->withErrors(['registro' => $mensaje])->withInput();
            }

            // Buscar id_usuario recién creado
            $u = DB::table('tbl_usuario as u')
                ->join('tbl_persona as p', 'p.id_persona', '=', 'u.id_persona')
                ->where('p.correo_institucional', $request->correo)
                ->select('u.id_usuario')
                ->first();

            if (!$u) {
                return redirect()->route('portal')
                    ->with('status', 'Usuario creado, pero no se pudo preparar activación por correo.');
            }

            // ✅ BITÁCORA: registro_usuario
            Bitacora::registrar(
                (int)$u->id_usuario,
                'registro_usuario',
                'Nuevo usuario registrado: '.$request->correo
            );

            // token 1 hora
            $token = Str::random(64);

            DB::table('email_verifications')->updateOrInsert(
                ['id_usuario' => $u->id_usuario],
                [
                    'token_hash' => hash('sha256', $token),
                    'expires_at' => now()->addMinutes(60),
                    'used_at' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $link = route('email.verify', ['token' => $token]);

            // ✅ Envío de correo con bitácora OK/FAIL
            try {
                Mail::to($request->correo)->send(new VerifyEmailMail($link));

                Bitacora::registrar(
                    (int)$u->id_usuario,
                    'email_verificacion_enviada',
                    'Se envió correo de verificación a '.$request->correo
                );
            } catch (\Throwable $e) {

                Bitacora::registrar(
                    (int)$u->id_usuario,
                    'email_verificacion_fallida',
                    'Fallo envío a '.$request->correo.' | '.$e->getMessage()
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
