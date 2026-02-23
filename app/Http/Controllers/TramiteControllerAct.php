<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TramiteControllerAct extends Controller
{
    public function actualizar(Request $request)
    {
        $request->validate([
            'id_tramite'         => 'required|integer',
            'id_persona'         => 'required|integer',
            'id_bitacora'        => 'required|integer',
            'nuevo_folio'        => 'required|string|max:50',
            'nueva_descripcion'  => 'required|string'
        ]);

        try {
            $resultado = DB::select(
                'CALL UPD_TRAMITE(?, ?, ?, ?, ?)',
                [
                    $request->id_tramite,
                    $request->id_persona,
                    $request->id_bitacora,
                    $request->nuevo_folio,
                    $request->nueva_descripcion
                ]
            );

            return response()->json([
                'resultado' => 'OK',
                'mensaje'   => $resultado[0]->mensaje ?? 'Actualización exitosa'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 400);
        }
    }
}
