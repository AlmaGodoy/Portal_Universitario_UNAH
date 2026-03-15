<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Bitacora; // Importamos el modelo de la bitacora

class BitacoraController extends Controller
{

    public function index(Request $request)
    {
        $fecha_inicial = $request->fecha_inicial;
        $fecha_final = $request->fecha_final;

        $bitacoras = collect();

        if ($fecha_inicial && $fecha_final) {
            try {
                $resultado = DB::select('CALL SEL_BITACORA_SISTEMA(?, ?)', [
                    $fecha_inicial,
                    $fecha_final
                ]);

                $coleccion = collect($resultado);

                $porPagina = 10;
                $paginaActual = LengthAwarePaginator::resolveCurrentPage();
                $itemsPagina = $coleccion->slice(($paginaActual - 1) * $porPagina, $porPagina)->values();

                $bitacoras = new LengthAwarePaginator(
                    $itemsPagina,
                    $coleccion->count(),
                    $porPagina,
                    $paginaActual,
                    [
                        'path' => $request->url(),
                        'query' => $request->query(),
                    ]
                );
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        } else {
            $bitacoras = new LengthAwarePaginator(
                collect(),
                0,
                10,
                1,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        }

        return view('bitacora', compact('bitacoras'));
    }

    // 1. Consulta
    public function ver($fecha_inicial, $fecha_final)
    {
        try {
            $resultado = DB::select('CALL SEL_BITACORA_SISTEMA(?, ?)', [$fecha_inicial,$fecha_final]);

            return response()->json($resultado, 201);
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
