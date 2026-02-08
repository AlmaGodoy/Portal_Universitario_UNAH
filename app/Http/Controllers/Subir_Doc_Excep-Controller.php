<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentoExcepcionalController extends Controller
{
    /**
     * Subir documento excepcional
     */
    public function subir(Request $request)
    {
        $request->validate([
            'id_persona' => 'required',
            'tipo_documento' => 'required',
            'nombre_documento' => 'required',
            'archivo' => 'required|file'
        ]);

        try {
            // PROCEDIMIENTO ALMACENADO INS_DOC_EXCEP
            $resultado = DB::select('CALL INS_CANCE_EXCEP(?, ?, ?, ?)', [
                $request->id_persona,
                $request->tipo_documento,
                $request->nombre_documento,
                $request->url_archivo
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
     * Eliminar documento excepcional (soft delete)
     * Procedimiento: SOFT_DEL_DOC_EXCEPCIONAL
     */
    public function eliminar($id)
    {
        try {
            $resultado = DB::select('CALL SOFT_DEL_DOC_EXCEPCIONAL(?)', [$id]);

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
     * Seleccionar cancelación excepcional
     * Procedimiento: SEL_CANCELACION_EXCEPCIONAL
     */
    public function obtenerCancelacion($id)
    {
        try {
            $resultado = DB::select('CALL SEL_CANCELACION_EXCEPCIONAL(?)', [$id]);

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
