<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonaController extends Controller
{
    /**
     * Agregar Personas
     * Procedimiento: INS_PERSONA
     */
    public function agregar(Request $request)
    {
        $validated = $request->validate([
            'id_persona' => 'required|integer',
            'nombre_persona' => 'required|string|max:150',
            'numero_documento' => 'required|integer',
            'correo_institucional' => 'required|email|max:150',
            'tipo_usuario' => 'required|string|max:50',
            'estado' => 'required|string|max:20'
        ]);

        try {

            $resultado = DB::select(
                'CALL INS_PERSONA(?, ?, ?, ?, ?, ?)',
                [
                    $validated['id_persona'],
                    $validated['nombre_persona'],
                    $validated['numero_documento'],
                    $validated['correo_institucional'],
                    $validated['tipo_usuario'],
                    $validated['estado']
                ]
            );

            if (!empty($resultado)) {

                return response()->json([
                    'resultado' => $resultado[0]->resultado ?? 'OK',
                    'mensaje' => $resultado[0]->mensaje ?? 'Persona agregada correctamente'
                ], 201);

            }

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'El procedimiento no devolvió respuesta'
            ], 500);

        } catch (\Exception $e) {

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'Error al agregar persona',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * ACTUALIZAR PERSONA
     * Procedimiento sugerido: UPD_PERSONA
     */
    public function actualizar(Request $request, $id)
    {
        if (!is_numeric($id)) {

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'ID inválido'
            ], 400);

        }

        $validated = $request->validate([
            'nombre_persona' => 'required|string|max:150',
            'numero_documento' => 'required|integer',
            'correo_institucional' => 'required|email|max:150',
            'tipo_usuario' => 'required|string|max:50',
            'estado' => 'required|string|max:20'
        ]);

        try {

            $resultado = DB::select(
                'CALL UPD_PERSONA(?, ?, ?, ?, ?, ?)',
                [
                    $id,
                    $validated['nombre_persona'],
                    $validated['numero_documento'],
                    $validated['correo_institucional'],
                    $validated['tipo_usuario'],
                    $validated['estado']
                ]
            );

            if (!empty($resultado)) {

                return response()->json([
                    'resultado' => $resultado[0]->resultado ?? 'OK',
                    'mensaje' => $resultado[0]->mensaje ?? 'Persona actualizada correctamente'
                ], 200);

            }

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No se pudo actualizar la persona'
            ], 500);

        } catch (\Exception $e) {

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'Error al actualizar persona',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Eliminar Persona (soft delete)
     * Procedimiento: SOFT_DEL_PERSONA
     */
    public function eliminar($id)
    {
        if (!is_numeric($id)) {

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'ID inválido'
            ], 400);

        }

        try {

            $resultado = DB::select(
                'CALL SOFT_DEL_PERSONA(?)',
                [$id]
            );

            if (!empty($resultado)) {

                return response()->json([
                    'resultado' => $resultado[0]->resultado ?? 'OK',
                    'mensaje' => $resultado[0]->mensaje ?? 'Persona eliminada correctamente'
                ], 200);

            }

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No se pudo eliminar la persona'
            ], 500);

        } catch (\Exception $e) {

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'Error al eliminar persona',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Seleccionar persona
     * Procedimiento: SEL_PERSONA
     */
    public function obtener($id)
    {
        if (!is_numeric($id)) {

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'ID inválido'
            ], 400);

        }

        try {

            $resultado = DB::select(
                'CALL SEL_PERSONA(?)',
                [$id]
            );

            if (empty($resultado)) {

                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje' => 'Persona no encontrada'
                ], 404);

            }

            return response()->json([
                'resultado' => 'OK',
                'data' => $resultado
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'Error al obtener persona',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
