<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TramiteController extends Controller
{
    /**
     * Crear un nuevo trámite académico
     * Procedimiento: INS_TRAMITE
     */
    public function crear(Request $request)
    {
        // 1. Validaciones básicas (no lógica de negocio)
        $request->validate([
            'id_persona'      => 'required|integer',
            'id_calendario'   => 'required|integer',
            'tipo_tramite'    => 'required|string|max:50',
            'prioridad'       => 'required|string|max:20',
            'valor_pago'      => 'required|numeric',
            'direccion'       => 'required|string|max:255',
            'id_usuario'      => 'required|integer'
        ]);

        try {
            // 2. Llamada al procedimiento almacenado
            $resultado = DB::select(
                'CALL INS_TRAMITE(?, ?, ?, ?, ?, ?, ?)',
                [
                    $request->id_persona,
                    $request->id_calendario,
                    $request->tipo_tramite,
                    $request->prioridad,
                    $request->valor_pago,
                    $request->direccion,
                    $request->id_usuario
                ]
            );

            // 3. Respuesta uniforme al frontend
            return response()->json([
                'resultado' => $resultado[0]->resultado,
                'mensaje'   => $resultado[0]->mensaje
            ], 201);

        } catch (\Exception $e) {

            // 4. Captura de errores del procedure o del motor SQL
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }
}
