<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CambioCarreraController extends Controller
{
    public function crear(Request $request)
    {
        $request->validate([
            'id_persona'         => 'required|integer',
            'id_calendario'      => 'required|integer',
            'id_carrera_destino' => 'required|integer',
            'direccion'          => 'required|string|max:255',
        ]);

        try {
            DB::statement('CALL INS_CAMBIO_CARRERA(?, ?, ?, ?, ?)', [
                $request->id_persona,
                $request->id_calendario,
                $request->id_carrera_destino,
                $request->direccion,
                12 // ID de usuario para bitácora
            ]);

            $ultimoTramite = DB::table('tbl_tramite')
                ->where('id_persona', $request->id_persona)
                ->orderByDesc('id_tramite')
                ->first();

            return response()->json([
                'resultado'  => 'OK',
                'mensaje'    => 'Trámite y bitácora registrados exitosamente',
                'id_tramite' => $ultimoTramite->id_tramite ?? null
            ], 201);

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

    public function calendarioVigente()
{
    try {

        $data = DB::select('CALL SEL_CALENDARIO_VIGENTE_CAMBIO_CARRERA()');

        return response()->json($data[0] ?? null, 200);

    } catch (\Throwable $e) {

        return response()->json([
            'resultado' => 'ERROR',
            'mensaje' => $e->getMessage()
        ], 500);
    }
    
}
public function carreras()
{
    try {
        $data = DB::select('CALL SEL_CARRERAS_ACTIVAS()');
        return response()->json($data, 200);
    } catch (\Throwable $e) {
        return response()->json([
            'resultado' => 'ERROR',
            'mensaje' => $e->getMessage()
        ], 500);
    }
}


public function listadoSecretaria()
{
    $tramites = DB::table('tbl_tramite as t')
        ->leftJoin('tbl_carrera as c', 't.id_carrera_destino', '=', 'c.id_carrera')
        ->leftJoin('tbl_pago as p', 't.id_tramite', '=', 'p.id_tramite')
        ->select(
            't.id_tramite',
            't.id_persona',
            't.fecha_solicitud',
            't.resolucion_de_tramite_academico as estado_tramite',
            'c.nombre_carrera as carrera_destino',
            'p.estado_pago'
        )
        ->where('t.tipo_tramite_academico', 'cambio_carrera')
        ->where('t.estado', 1)
        ->orderByDesc('t.id_tramite')
        ->get();

    return response()->json($tramites);
}



}



