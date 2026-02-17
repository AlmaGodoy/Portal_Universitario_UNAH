<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    // 1) Crear pago (pendiente)
    public function crear(Request $request)
    {
        $request->validate([
            'id_tramite'       => 'required|integer',
            'id_banco'         => 'required|integer',
            'referencia_banco' => 'required|string|max:150',
            'monto'            => 'required|numeric',
            'observaciones'    => 'nullable|string',
        ]);

        try {
            // Debes tener creado el SP INS_PAGO_TRAMITE
            $data = DB::select('CALL INS_PAGO_TRAMITE(?, ?, ?, ?, ?)', [
                $request->id_tramite,
                $request->id_banco,
                $request->referencia_banco,
                $request->monto,
                $request->observaciones
            ]);

            return response()->json($data[0] ?? $data, 201);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage(),
            ], 500);
        }
    }

    // 2) Ver pagos por trámite (tu SP SEL_PAGOS_POR_TRAMITE)
    public function verPorTramite($id_tramite)
    {
        try {
            $data = DB::select('CALL SEL_PAGOS_POR_TRAMITE(?)', [$id_tramite]);
            return response()->json($data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage(),
            ], 500);
        }
    }

    // 3) Actualizar estado del pago (validado/rechazado/pendiente)
    public function actualizarEstado(Request $request, $id_pago)
    {
        $request->validate([
            'estado_pago'   => 'required|in:pendiente,validado,rechazado',
            'observaciones' => 'nullable|string',
        ]);

        try {
            // Debes tener creado el SP UPD_PAGO_ESTADO
            $data = DB::select('CALL UPD_PAGO_ESTADO(?, ?, ?)', [
                $id_pago,
                $request->estado_pago,
                $request->observaciones
            ]);

            return response()->json($data[0] ?? $data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage(),
            ], 500);
        }
    }
}
