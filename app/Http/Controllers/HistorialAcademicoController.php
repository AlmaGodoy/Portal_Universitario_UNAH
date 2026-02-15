<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistorialAcademicoController extends Controller
{
    // Crear historial
    public function crear(Request $request)
    {
        $request->validate([
            'id_persona'       => 'required|integer',
            'clases_aprobadas' => 'required|integer|min:0',
        ]);

        try {
            $data = DB::select('CALL INS_HISTORIAL_ACADEMICO(?, ?)', [
                $request->id_persona,
                $request->clases_aprobadas
            ]);

            return response()->json($data[0] ?? $data, 201);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // Ver historial de una persona
    public function ver($id_persona)
    {
        try {
            $data = DB::select('CALL SEL_HISTORIAL_ACADEMICO(?)', [$id_persona]);
            return response()->json($data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar clases aprobadas (por id_persona)
    public function actualizar(Request $request, $id_persona)
    {
        $request->validate([
            'clases_aprobadas' => 'required|integer|min:0',
        ]);

        try {
            $data = DB::select('CALL UPD_HISTORIAL_ACADEMICO(?, ?)', [
                $id_persona,
                $request->clases_aprobadas
            ]);

            return response()->json($data[0] ?? $data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // Soft delete por id_historial
    public function eliminar($id_historial)
    {
        try {
            $data = DB::select('CALL SOFT_DEL_HISTORIAL_ACADEMICO(?)', [$id_historial]);
            return response()->json($data[0] ?? $data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }
}
