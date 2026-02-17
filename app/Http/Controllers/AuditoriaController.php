<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Auditoria; // Importamos el modelo de la bitacora

class AuditoriaController extends Controller
{
    // 1. Consulta
    public function ver($fecha_inicial, $fecha_final)
    {
        try {
            $resultado = DB::select('CALL SEL_AUDITORIA_SISTEMA(?, ?)', [$fecha_inicial,$fecha_final]);

            return response()->json($resultado[0], 201);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // 4. ELIMINAR
    public function eliminar($id_auditoria)
    {
        try {
            $resultado = DB::select('CALL SOFT_DEL_AUDITORIA(?)', [$id_auditoria]);
            return response()->json($resultado[0], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

        // 4. ELIMINAR
    public function actualizar($id_auditoria,$id_usuario,$id_objeto,$descripcion)
    {
        try {
            $resultado = DB::select('CALL UPD_AUDITORIA(?,?,?,?)', [$id_auditoria,$id_usuario,$id_objeto,$descripcion]);
            return response()->json($resultado[0], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }
}
