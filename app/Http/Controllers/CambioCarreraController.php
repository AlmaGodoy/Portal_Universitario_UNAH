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
        ->leftJoin('tbl_persona as p', 't.id_persona', '=', 'p.id_persona')
        ->leftJoin('tbl_carrera as c', 't.id_carrera_destino', '=', 'c.id_carrera')
        ->leftJoin('tbl_pago as pg', 't.id_tramite', '=', 'pg.id_tramite')
        ->select(
            't.id_tramite',
            'p.nombre_persona as nombre_persona',
            't.fecha_solicitud',
            't.resolucion_de_tramite_academico as estado_tramite',
            'c.nombre_carrera as carrera_destino',
            'pg.estado_pago'
        )
        ->where('t.tipo_tramite_academico', 'cambio_carrera')
        ->where('t.estado', 1)
        ->orderByDesc('t.id_tramite')
        ->get();

    return response()->json($tramites);
}

 public function detalleSecretaria($id_tramite)
    {
        $tramite = DB::table('tbl_tramite as t')

            ->leftJoin('tbl_persona as p', 't.id_persona', '=', 'p.id_persona')

            ->leftJoin('tbl_estudiante as e', 't.id_persona', '=', 'e.id_persona')

            ->leftJoin('tbl_carrera as c', 't.id_carrera_destino', '=', 'c.id_carrera')

            ->leftJoin('tbl_pago as pg', 't.id_tramite', '=', 'pg.id_tramite')

            ->select(
                't.id_tramite',
                't.id_persona',
                't.fecha_solicitud',
                't.direccion',
                't.resolucion_de_tramite_academico as estado_tramite',
                'p.nombre_persona as estudiante',
                'c.nombre_carrera as carrera_destino',
                'e.indice_periodo',
                'e.indice_global',
                'e.cantidad_clases_aprobadas',
                'pg.id_pago',
                'pg.estado_pago',
                'pg.observaciones_pago'
            )
            ->where('t.id_tramite', $id_tramite)

            ->orderByDesc('pg.id_pago')
            ->first();

        return response()->json($tramite);
    }

    /*
        =========================================================
        GUARDAR REVISIÓN DE SECRETARÍA
        =========================================================
        
    */
    public function guardarRevisionSecretaria(Request $request)
    {
        $request->validate([
            'id_tramite' => 'required|integer',
            'indice_periodo' => 'nullable|numeric',
            'indice_global' => 'nullable|numeric',
            'clases_aprobadas' => 'nullable|integer',
            'estado_pago' => 'required|in:validado,pendiente,rechazado',
            'observaciones_pago' => 'nullable|string'
        ]);

        
        $tramite = DB::table('tbl_tramite')
            ->where('id_tramite', $request->id_tramite)
            ->first();

        if (!$tramite) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No se encontró el trámite.'
            ], 404);
        }

        
        DB::table('tbl_estudiante')
            ->where('id_persona', $tramite->id_persona)
            ->update([
                'indice_periodo' => $request->indice_periodo,
                'indice_global' => $request->indice_global,
                'cantidad_clases_aprobadas' => $request->clases_aprobadas
            ]);

      
        $pago = DB::table('tbl_pago')
            ->where('id_tramite', $request->id_tramite)
            ->orderByDesc('id_pago')
            ->first();

        if ($pago) {
            DB::table('tbl_pago')
                ->where('id_pago', $pago->id_pago)
                ->update([
                    'estado_pago' => $request->estado_pago,
                    'observaciones_pago' => $request->observaciones_pago
                ]);
        }

    
        DB::table('tbl_tramite')
            ->where('id_tramite', $request->id_tramite)
            ->update([
                'resolucion_de_tramite_academico' => 'revision'
            ]);

        return response()->json([
            'resultado' => 'OK',
            'mensaje' => 'Revisión de Secretaría guardada correctamente.'
        ]);
    }

    public function listadoCoordinacion()
{
    $tramites = DB::table('tbl_tramite as t')
        ->leftJoin('tbl_persona as p', 't.id_persona', '=', 'p.id_persona')
        ->leftJoin('tbl_carrera as c', 't.id_carrera_destino', '=', 'c.id_carrera')
        ->leftJoin('tbl_pago as pg', 't.id_tramite', '=', 'pg.id_tramite')
        ->select(
            't.id_tramite',
            'p.nombre_persona as nombre_persona',
            't.fecha_solicitud',
            't.resolucion_de_tramite_academico as estado_tramite',
            'c.nombre_carrera as carrera_destino',
            'pg.estado_pago'
        )
        ->where('t.tipo_tramite_academico', 'cambio_carrera')
        ->where('t.estado', 1)
        ->orderByDesc('t.id_tramite')
        ->get();

    return response()->json($tramites);
}

/*
    =========================================================
    DICTAMEN FINAL DE COORDINACIÓN
    =========================================================
    
*/
public function dictaminarCoordinacion(Request $request, $id_tramite)
{
    $request->validate([
        'estado' => 'required|in:aprobada,rechazada',
        'observacion_dictamen' => 'nullable|string'
    ]);

    $tramite = DB::table('tbl_tramite')
        ->where('id_tramite', $id_tramite)
        ->first();

    if (!$tramite) {
        return response()->json([
            'resultado' => 'ERROR',
            'mensaje' => 'No se encontró el trámite.'
        ], 404);
    }

    DB::table('tbl_tramite')
        ->where('id_tramite', $id_tramite)
        ->update([
            'resolucion_de_tramite_academico' => $request->estado,
            'observacion_dictamen' => $request->observacion_dictamen
        ]);

    return response()->json([
        'resultado' => 'OK',
        'mensaje' => 'Dictamen final guardado correctamente.'
    ]);
}



}



