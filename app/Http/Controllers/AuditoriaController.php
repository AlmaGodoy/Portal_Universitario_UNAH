<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $fechaInicial = $request->input('fecha_inicial');
        $fechaFinal   = $request->input('fecha_final');
        $vista        = $this->obtenerVistaAuditoria();

        try {
            $resultado = DB::select('CALL SEL_AUDITORIA(?, ?)', [
                $fechaInicial,
                $fechaFinal
            ]);

            $collection = collect($resultado);

            $perPage = 10;
            $page = LengthAwarePaginator::resolveCurrentPage();

            $items = $collection
                ->slice(($page - 1) * $perPage, $perPage)
                ->values();

            $registros = new LengthAwarePaginator(
                $items,
                $collection->count(),
                $perPage,
                $page,
                [
                    'path'  => $request->url(),
                    'query' => $request->query(),
                ]
            );

            return view($vista, compact(
                'registros',
                'fechaInicial',
                'fechaFinal'
            ));
        } catch (\Throwable $e) {
            $registros = new LengthAwarePaginator(
                collect([]),
                0,
                10,
                1,
                [
                    'path'  => $request->url(),
                    'query' => $request->query(),
                ]
            );

            return view($vista, compact(
                'registros',
                'fechaInicial',
                'fechaFinal'
            ))->with(
                'error',
                'Error al consultar la auditoría: ' . $e->getMessage()
            );
        }
    }

    // 1. Consulta
    public function ver($fecha_inicial, $fecha_final)
    {
        try {
            $registros = DB::select('CALL SEL_AUDITORIA(?, ?)', [
                $fecha_inicial,
                $fecha_final
            ]);

            return response()->json($registros, 200);
        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // 2. INGRESAR
    public function ingresar($p_id_usuario, $p_id_objeto, $p_accion, $p_descripcion, $p_fecha)
    {
        try {
            $registros = DB::select('CALL INS_AUDITORA(?,?,?,?,?)', [
                $p_id_usuario,
                $p_id_objeto,
                $p_accion,
                $p_descripcion,
                $p_fecha
            ]);

            return response()->json($registros[0] ?? [
                'resultado' => 'OK',
                'mensaje'   => 'Auditoría ingresada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // ELIMINAR
    public function eliminar($id_auditoria)
    {
        try {
            $registros = DB::select('CALL SOFT_DEL_AUDITORIA(?)', [$id_auditoria]);

            return response()->json($registros[0] ?? [
                'resultado' => 'OK',
                'mensaje'   => 'Auditoría eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // ACTUALIZAR
    public function actualizar($id_auditoria, $id_usuario, $id_objeto, $descripcion)
    {
        try {
            $registros = DB::select('CALL UPD_AUDITORIA(?,?,?,?)', [
                $id_auditoria,
                $id_usuario,
                $id_objeto,
                $descripcion
            ]);

            return response()->json($registros[0] ?? [
                'resultado' => 'OK',
                'mensaje'   => 'Auditoría actualizada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    private function obtenerVistaAuditoria(): string
    {
        $rol = strtolower(session('rol_texto', ''));

        return match ($rol) {
            'coordinador' => 'auditoria_coordinador',

            'secretaria_academica',
            'secretario_academico',
            'secretario académico',
            'secretaría académica' => 'auditoria_secretaria_academica',

            'secretaria_general',
            'secretario_general',
            'secretario general',
            'secretaría general' => 'auditoria_secretaria_general',

            default => 'auditoria_coordinador',
        };
    }
}