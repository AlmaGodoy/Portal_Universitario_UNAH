<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PerfilEstudianteController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        $perfil = $this->obtenerDatosPerfil($usuario);
        $resumenTramites = $this->obtenerResumenTramites($usuario);

        return view('mi-perfil', compact(
            'usuario',
            'perfil',
            'resumenTramites'
        ));
    }

    private function obtenerDatosPerfil($usuario)
    {
        $persona = null;
        $estudiante = null;
        $carrera = null;

        if ($usuario && !empty($usuario->id_persona)) {

            if (Schema::hasTable('tbl_persona')) {
                $persona = DB::table('tbl_persona')
                    ->where('id_persona', $usuario->id_persona)
                    ->first();
            }

            if (Schema::hasTable('tbl_estudiante')) {
                $estudiante = DB::table('tbl_estudiante')
                    ->where('id_persona', $usuario->id_persona)
                    ->first();
            }

            if ($estudiante && !empty($estudiante->id_carrera) && Schema::hasTable('tbl_carrera')) {
                $carrera = DB::table('tbl_carrera')
                    ->where('id_carrera', $estudiante->id_carrera)
                    ->first();
            }
        }

        $nombreCompleto = $this->obtenerPrimerValor([
            $persona->nombre_persona ?? null,
            $persona->nombre_completo ?? null,
            $usuario->nombre_persona ?? null,
            $usuario->name ?? null,
        ], 'No disponible');

        $correo = $this->obtenerPrimerValor([
            $usuario->email ?? null,
            $usuario->correo_institucional ?? null,
            $persona->correo_institucional ?? null,
            $persona->correo ?? null,
        ], 'No disponible');

        $numeroCuenta = $this->obtenerPrimerValor([
            $estudiante->numero_cuenta ?? null,
            $estudiante->num_cuenta ?? null,
            $estudiante->cuenta ?? null,
            $estudiante->no_cuenta ?? null,
        ], 'No disponible');

        $identidad = $this->obtenerPrimerValor([
            $persona->identidad ?? null,
            $persona->numero_identidad ?? null,
            $persona->num_identidad ?? null,
            $persona->dni ?? null,
        ], 'No disponible');

        $telefono = $this->obtenerPrimerValor([
            $persona->telefono ?? null,
            $persona->celular ?? null,
            $persona->telefono_persona ?? null,
        ], 'No disponible');

        $direccion = $this->obtenerPrimerValor([
            $persona->direccion ?? null,
            $persona->municipio ?? null,
            $persona->direccion_persona ?? null,
        ], 'No disponible');

        $nombreCarrera = $this->obtenerPrimerValor([
            $carrera->nombre_carrera ?? null,
            $carrera->carrera ?? null,
            $carrera->descripcion ?? null,
        ], 'No disponible');

        $ultimaVerificacion2FA = $this->formatearFecha(
            $usuario->twofa_verified_at ?? null
        );

        $ultimoInicioSesion = $this->formatearFecha(
            $usuario->ultimo_inicio_sesion
            ?? $usuario->last_login_at
            ?? null
        );

        return [
            'nombre_completo' => $nombreCompleto,
            'correo' => $correo,
            'email' => $correo,

            'numero_cuenta' => $numeroCuenta,
            'identidad' => $identidad,
            'telefono' => $telefono,
            'direccion' => $direccion,

            'carrera_actual' => $nombreCarrera,
            'carrera' => $nombreCarrera,
            'facultad' => 'Facultad de Ciencias Económicas, Administrativas y Contables',
            'centro_universitario' => 'Ciudad Universitaria',
            'estado_estudiante' => 'Activo',

            'correo_verificado' => !empty($usuario->email_verified_at),
            'twofa_activado' => !empty($usuario->twofa_verified_at),
            'twofa_verified_at' => $ultimaVerificacion2FA,
            'ultima_verificacion_2fa' => $ultimaVerificacion2FA,
            'ultimo_inicio_sesion' => $ultimoInicioSesion,
        ];
    }

    private function obtenerResumenTramites($usuario)
    {
        $resumen = [
            'enviados' => 0,
            'pendientes' => 0,
            'aprobados' => 0,
            'rechazados' => 0,
        ];

        if (!$usuario || empty($usuario->id_persona)) {
            return $resumen;
        }

        if (!Schema::hasTable('tbl_tramite')) {
            return $resumen;
        }

        $consulta = DB::table('tbl_tramite')
            ->where('id_persona', $usuario->id_persona);

        $resumen['enviados'] = (clone $consulta)->count();

        if (!Schema::hasColumn('tbl_tramite', 'estado')) {
            return $resumen;
        }

        $tramites = (clone $consulta)->get();

        foreach ($tramites as $tramite) {
            $estado = strtolower(trim($tramite->estado ?? ''));

            if (in_array($estado, [
                'pendiente',
                'en revision',
                'en revisión',
                'revision',
                'revisión',
                'en proceso',
                'proceso'
            ])) {
                $resumen['pendientes']++;
            }

            if (in_array($estado, [
                'aprobado',
                'aprobada'
            ])) {
                $resumen['aprobados']++;
            }

            if (in_array($estado, [
                'rechazado',
                'rechazada'
            ])) {
                $resumen['rechazados']++;
            }
        }

        return $resumen;
    }

    private function obtenerPrimerValor(array $valores, $default = 'No disponible')
    {
        foreach ($valores as $valor) {
            if ($valor !== null && trim((string) $valor) !== '') {
                return trim((string) $valor);
            }
        }

        return $default;
    }

    private function formatearFecha($fecha)
    {
        if (empty($fecha)) {
            return 'No disponible';
        }

        try {
            return date('d/m/Y H:i', strtotime($fecha));
        } catch (\Throwable $e) {
            return 'No disponible';
        }
    }
}