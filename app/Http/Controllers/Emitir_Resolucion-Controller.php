<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Emitir_ResolucionController extends Controller
{
    /**
     * Emitir Resoluciones
     */
    public function emitir(Request $request)
    {
        $request->validate([
            'id_resolucion' => 'required',
            'id_tramite' => 'required',
            'id_coordinador' => 'required',
            'estado_validacion' => 'required',
            'observaciones' => 'required',
            'fecha_resolucion' => 'required',
            'fecha_anulacion' => 'required',
            'documento_resolucion' => 'required',
            'activo' => 'required'
        ]);

        try {
            // PROCEDIMIENTO ALMACENADO INS_EMITIR_RESOLUCION
            $resultado = DB::select('CALL INS_EMITIR_RESOLUCION(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->id_resolucion,
                $request->id_tramite,
                $request->id_coordinador,
                $request->estado_validacion,
                $request->observaciones,
                $request->fecha_resolucion,
                $request->fecha_anulacion,
                $request->documento_resolucion,
                $request->activo
            ]);

            return response()->json([
                'resultado' => $resultado[0]->resultado,
                'mensaje' => $resultado[0]->mensaje
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar Resolucion (soft delete)
     * Procedimiento: SOFT_DEL_RESOLUCION
     */
    public function eliminar($id)
    {
        try {
            $resultado = DB::select('CALL SOFT_DEL_RESOLUCION(?)', [$id]);

            return response()->json([
                'resultado' => $resultado[0]->resultado,
                'mensaje' => $resultado[0]->mensaje
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Seleccionar resolucion
     * Procedimiento: SEL_EMITIR_RESOLUCION
     */
    public function obtenerResolucion($id)
    {
        try {
            $resultado = DB::select('CALL SEL_EMITIR_RESOLUCION(?)', [$id]);

            return response()->json([
                'data' => $resultado
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }
}
