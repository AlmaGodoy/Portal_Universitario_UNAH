<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Bitacora; // Importamos el modelo de la bitacora

class BitacoraController extends Controller
{
    // 1. Consulta
    public function ver($fecha_inicial, $fecha_final)
    {
        try {
            $resultado = DB::select('CALL SEL_BITACORA_SISTEMA(?, ?)', [$fecha_inicial,$fecha_final]);

            return response()->json($resultado[0], 201);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }


        // 2. INGRESAR
    public function ingresar($id_usuario,$id_objeto,$p_accion,$fecha_accion,$p_descripcion)
    {
        try {
            $resultado = DB::select('CALL INS_BITACORA(?,?,?,?,?)', [$id_usuario,$id_objeto,$p_accion,$fecha_accion,$p_descripcion]);
            return response()->json($resultado[0], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }
}
