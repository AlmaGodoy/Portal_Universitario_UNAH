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
    public function ingresar($id_bitacora,$id_objeto,$accion,$fecha_accion,$descripcion)
    {
        try {
            $resultado = DB::select('CALL INS_BITACORA(?,?,?,?,?)', [$id_bitacora,$id_objeto,$accion,$fecha_accion,$descripcion]);
            return response()->json($resultado[0], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }
}
