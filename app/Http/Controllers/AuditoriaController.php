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
            $resultado = DB::select('CALL SEL_AUDITORIA(?, ?)', [$fecha_inicial,$fecha_final]);

            return response()->json($resultado[0], 201);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // 2. INGRESAR
    public function ingresar($p_id_usuario,$p_id_objeto,$p_accion,$p_descripcion,$p_fecha)
    {
        try {
            $resultado = DB::select('CALL INS_AUDITORA(?,?,?,?,?)', [$p_id_usuario,$p_id_objeto,$p_accion,$p_descripcion,$p_fecha]);
            return response()->json($resultado[0], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // ELIMINAR
    public function eliminar($id_auditoria)
    {
        try {
            $resultado = DB::select('CALL SOFT_DEL_AUDITORIA(?)', [$id_auditoria]);
            return response()->json($resultado[0], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

        // ACTUALIZAR
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
