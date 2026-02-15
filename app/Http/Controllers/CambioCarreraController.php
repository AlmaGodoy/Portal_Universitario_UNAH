<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CambioCarreraController extends Controller
{
    // Crear solicitud de cambio de carrera
    public function crear(Request $request)
    {
        $request->validate([
            'id_persona'    => 'required|integer',
            'id_calendario' => 'required|integer',
            'direccion'     => 'required|string|max:255',
        ]);

        try {
            $data = DB::select('CALL INS_CAMBIO_CARRERA(?, ?, ?)', [
                $request->id_persona,
                $request->id_calendario,
                $request->direccion
            ]);

            return response()->json($data[0] ?? $data, 201);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // Consultar (según tu SP: por id_tramite o id_persona) con accion='tramite'
    public function ver($codigo)
    {
        try {
            $data = DB::select('CALL SEL_CAMBIO_CARRERA(?, ?)', [
                'tramite',
                $codigo
            ]);

            return response()->json($data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar estado/resolución del trámite (pendiente/revision/aprobada/rechazada, etc.)
    public function actualizarEstado(Request $request, $id_tramite)
    {
        $request->validate([
            'estado' => 'required|string|max:20',
        ]);

        try {
            $data = DB::select('CALL UPD_CAMBIO_CARRERA(?, ?)', [
                $id_tramite,
                $request->estado
            ]);

            return response()->json($data[0] ?? $data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // Soft delete / inactivar
    public function eliminar($id_tramite)
    {
        try {
            $data = DB::select('CALL SOFT_DEL_CAMBIO_CARRERA(?)', [$id_tramite]);
            return response()->json($data[0] ?? $data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }
}
