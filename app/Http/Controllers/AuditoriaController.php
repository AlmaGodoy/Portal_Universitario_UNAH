<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Auditoria; // Importamos el modelo de la bitacora

class AuditoriaController extends Controller
{

    public function index(Request $request)
    {

    $fechaInicial = $request->input('fecha_inicial');
    $fechaFinal   = $request->input('fecha_final');

    try {

        $resultado = DB::select('CALL SEL_AUDITORIA(?, ?)', [
            $fechaInicial,
            $fechaFinal
        ]);

        // Convertir a colección
        $collection = collect($resultado);

        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $items = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        $registros = new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('auditoria', compact(
            'registros',
            'fechaInicial',
            'fechaFinal'
        ));

    } catch (\Throwable $e) {

        $registros = new LengthAwarePaginator(
            collect([]),
            0,
            10,
            1,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('auditoria', compact(
            'registros',
            'fechaInicial',
            'fechaFinal'
        ))->with('error',
            'Error al consultar la auditoría: ' . $e->getMessage()
        );
    }

    }

    // 1. Consulta
    public function ver($fecha_inicial, $fecha_final)
    {
        try {
            $registros = DB::select('CALL SEL_AUDITORIA(?, ?)', [$fecha_inicial,$fecha_final]);

            return response()->json($registros[0], 201);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // 2. INGRESAR
    public function ingresar($p_id_usuario,$p_id_objeto,$p_accion,$p_descripcion,$p_fecha)
    {
        try {
            $registros = DB::select('CALL INS_AUDITORA(?,?,?,?,?)', [$p_id_usuario,$p_id_objeto,$p_accion,$p_descripcion,$p_fecha]);
            return response()->json($registros[0], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // ELIMINAR
    public function eliminar($id_auditoria)
    {
        try {
            $registros = DB::select('CALL SOFT_DEL_AUDITORIA(?)', [$id_auditoria]);
            return response()->json($registros[0], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

        // ACTUALIZAR
    public function actualizar($id_auditoria,$id_usuario,$id_objeto,$descripcion)
    {
        try {
            $registros = DB::select('CALL UPD_AUDITORIA(?,?,?,?)', [$id_auditoria,$id_usuario,$id_objeto,$descripcion]);
            return response()->json($registros[0], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }
}
