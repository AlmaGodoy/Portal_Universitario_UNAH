<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteTramiteController extends Controller
{
    /**
     * Reporte general de trámites académicos
     * Procedimiento: SEL_REPORTE_TRAMITE
     */
    public function reporte(Request $request)
    {
        // 1. Validaciones suaves (porque los filtros son opcionales)
        $request->validate([
            'tipo_tramite'       => 'nullable|string|max:50',
            'estado_resolucion'  => 'nullable|string|max:20'
        ]);

        try {
            // 2. Llamada al procedimiento almacenado
            $resultado = DB::select(
                'CALL SEL_REPORTE_TRAMITE(?, ?)',
                [
                    $request->tipo_tramite,
                    $request->estado_resolucion
                ]
            );

            // 3. Respuesta estructurada
            return response()->json([
                'total_registros' => count($resultado),
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
