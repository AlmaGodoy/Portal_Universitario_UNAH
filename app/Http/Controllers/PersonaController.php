<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonaController extends Controller
{
    /**
     * Gestionar Personas
     */
    public function gestionar(Request $request)
    {
        $request->validate([
            'id_persona' => 'required',
            'nombre_persona' => 'required',
            'numero_documento' => 'required',
            'correo_institucional' => 'required',
            'tipo_usuario' => 'required',
            'estado' => 'required'
        ]);

        try {
            // PROCEDIMIENTO ALMACENADO INS_PERSONA
            $resultado = DB::select('CALL INS_PERSONA(?, ?, ?, ?, ?, ?)', [
                $request->id_persona,
                $request->nombre_persona,
                $request->numero_documento,
                $request->correo_institucional,
                $request->tipo_usuario,
                $request->estado
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
     * Eliminar Persona (soft delete)
     * Procedimiento: SOFT_DEL_PERSONA
     */
    public function eliminar($id)
    {
        try {
            $resultado = DB::select('CALL SOFT_DEL_PERSONA(?)', [$id]);

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
     * Seleccionar persona
     * Procedimiento: SEL_PERSONA
     */
    public function obtenerPersona($id)
    {
        try {
            $resultado = DB::select('CALL SEL_PERSONA(?)', [$id]);

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
