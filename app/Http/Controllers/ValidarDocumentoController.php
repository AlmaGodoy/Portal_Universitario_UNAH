<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class ValidarDocumentoController extends Controller
{
    /**Listar trámites pendientes de validación*/
public function listarPendientes()
    {
        try {
            $pendientes = DB::select('CALL SEL_VAL_TRAMITE()');

            return response()->json([
                'resultado' => 'OK',
                'datos' => $pendientes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'Error al obtener pendientes: ' . $e->getMessage()
            ], 500);
        }
    }
 /**Aprobar trámite / Documento
  */
 public function aprobar(Request $request)
    {
        // Validamos que el ID del trámite venga en la petición
        $request->validate([
            'id_tramite' => 'required|integer'
        ]);

        try {
            $resultado = DB::select('CALL UPD_APROBAR_CANCELACION_CLA8E8(?)', [
                $request->id_tramite
            ]);

            return response()->json([
                'resultado' => 'OK',
                'mensaje' => 'Trámite aprobado con éxito',
                'detalle' => $resultado[0] ?? null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'Error al aprobar: ' . $e->getMessage()
            ], 500);
        }
    }
    /**Devolver trámite por errrores*/
    public function devolver(Request $request)
    {
        $request->validate([
            'id_tramite' => 'required|integer'
        ]);

        try {
            $resultado = DB::select('CALL UPD_DEVOLVER_CANCELACION_CLA8E8(?)', [
                $request->id_tramite
            ]);

            return response()->json([
                'resultado' => 'OK',
                'mensaje' => 'El trámite ha sido devuelto para corrección',
                'detalle' => $resultado[0] ?? null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'Error al devolver el trámite: ' . $e->getMessage()
            ], 500);
        }
    }
}
