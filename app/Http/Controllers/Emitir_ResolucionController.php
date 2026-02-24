<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Emitir_ResolucionController extends Controller
{
    /**
     * Emitir Resolución
     * Procedimiento: INS_EMITIR_RESOLUCION
     */
    public function emitir(Request $request)
    {
        $validated = $request->validate([
            'id_resolucion'         => 'required|integer',
            'id_tramite'            => 'required|integer',
            'id_coordinador'        => 'required|integer',
            'estado_validacion'     => 'required|string|max:50',
            'observaciones'         => 'required|string|max:500',
            'fecha_resolucion'      => 'required|date',
            'fecha_anulacion'       => 'nullable|date',
            'documento_resolucion'  => 'required|string|max:255',
            'estado'                => 'required|string|max:20'
        ]);

        try {

            $resultado = DB::select(
                'CALL INS_EMITIR_RESOLUCION(?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $validated['id_resolucion'],
                    $validated['id_tramite'],
                    $validated['id_coordinador'],
                    $validated['estado_validacion'],
                    $validated['observaciones'],
                    $validated['fecha_resolucion'],
                    $validated['fecha_anulacion'],
                    $validated['documento_resolucion'],
                    $validated['estado']
                ]
            );

            if (!empty($resultado)) {
                return response()->json([
                    'resultado' => $resultado[0]->resultado ?? 'OK',
                    'mensaje'   => $resultado[0]->mensaje ?? 'Resolución emitida correctamente'
                ], 201);
            }

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'El procedimiento no devolvió respuesta'
            ], 500);

        } catch (\Exception $e) {

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'Error al emitir resolución',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Actualizar Resolución
     * Procedimiento: UPD_EMITIR_RESOLUCION
     */
    public function actualizar(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'ID inválido'
            ], 400);
        }

        $validated = $request->validate([
            'id_tramite'            => 'required|integer',
            'id_coordinador'        => 'required|integer',
            'estado_validacion'     => 'required|string|max:50',
            'observaciones'         => 'required|string|max:500',
            'fecha_resolucion'      => 'required|date',
            'fecha_anulacion'       => 'nullable|date',
            'documento_resolucion'  => 'required|string|max:255',
            'estado'                => 'required|string|max:20'
        ]);

        try {

            $resultado = DB::select(
                'CALL UPD_EMITIR_RESOLUCION(?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $id,
                    $validated['id_tramite'],
                    $validated['id_coordinador'],
                    $validated['estado_validacion'],
                    $validated['observaciones'],
                    $validated['fecha_resolucion'],
                    $validated['fecha_anulacion'],
                    $validated['documento_resolucion'],
                    $validated['estado']
                ]
            );

            if (!empty($resultado)) {
                return response()->json([
                    'resultado' => $resultado[0]->resultado ?? 'OK',
                    'mensaje'   => $resultado[0]->mensaje ?? 'Resolución actualizada correctamente'
                ], 200);
            }

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No se pudo actualizar la resolución'
            ], 500);

        } catch (\Exception $e) {

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'Error al actualizar resolución',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Eliminar Resolución (Soft Delete)
     * Procedimiento: SOFT_DEL_RESOLUCION
     */
    public function eliminar($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'ID inválido'
            ], 400);
        }

        try {

            $resultado = DB::select(
                'CALL SOFT_DEL_RESOLUCION(?)',
                [$id]
            );

            if (!empty($resultado)) {
                return response()->json([
                    'resultado' => $resultado[0]->resultado ?? 'OK',
                    'mensaje'   => $resultado[0]->mensaje ?? 'Resolución eliminada correctamente'
                ], 200);
            }

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No se pudo eliminar la resolución'
            ], 500);

        } catch (\Exception $e) {

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'Error al eliminar resolución',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Obtener una Resolución por ID
     * Procedimiento: SEL_EMITIR_RESOLUCION
     */
    public function obtener($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'ID inválido'
            ], 400);
        }

        try {

            $resultado = DB::select(
                'CALL SEL_EMITIR_RESOLUCION(?)',
                [$id]
            );

            if (empty($resultado)) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje' => 'Resolución no encontrada'
                ], 404);
            }

            return response()->json([
                'resultado' => 'OK',
                'data' => $resultado
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'Error al obtener resolución',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Listar todas las resoluciones
     * Procedimiento: SEL_RESOLUCIONES
     */
    public function listar()
    {
        try {

            $resultado = DB::select('CALL SEL_RESOLUCIONES()');

            return response()->json([
                'resultado' => 'OK',
                'data' => $resultado
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'Error al listar resoluciones',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
