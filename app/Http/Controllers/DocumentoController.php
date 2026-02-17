<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    // 🔹 Subir documento
    public function crear(Request $request)
    {
        $request->validate([
            'id_tramite'     => 'required|integer',
            'tipo_documento' => 'required|string|max:50',
            'archivo'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        try {

            // Guardar archivo en storage/app/public/documentos
            $ruta = $request->file('archivo')->store('documentos', 'public');

            $nombreArchivo = $request->file('archivo')->getClientOriginalName();
            $hash = hash_file('sha256', $request->file('archivo'));

            $data = DB::select('CALL INS_SUBIR_DOCUMENTO(?, ?, ?, ?, ?)', [
            $request->id_tramite,
            $request->tipo_documento,
            $nombreArchivo, // nombre_documento
            $hash,
            $ruta
            ]);

            return response()->json($data[0] ?? $data, 201);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // 🔹 Ver documentos por trámite
    public function ver($id_tramite)
    {
        try {

            $data = DB::select('CALL SEL_SUBIR_DOCUMENTO(?)', [$id_tramite]);

            return response()->json($data, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // 🔹 Actualizar documento (autenticidad / estado)
    public function actualizar(Request $request, $id_documento)
    {
        $request->validate([
            'autenticidad_documento' => 'nullable|string|max:50',
            'estado'                 => 'nullable|integer|in:0,1'
        ]);

        try {

            $data = DB::select('CALL UPD_SUBIR_DOCUMENTO(?, ?)', [
                $id_documento,
                $request->autenticidad_documento
           ]);


            return response()->json($data[0] ?? $data, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // 🔹 Soft delete documento
    public function eliminar($id_documento)
    {
        try {

            $data = DB::select('CALL SOFT_DEL_SUBIR_DOCUMENTO(?)', [$id_documento]);

            return response()->json($data[0] ?? $data, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }
}
