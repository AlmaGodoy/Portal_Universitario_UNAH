<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Documento; // Importamos el modelo

class DocumentoExcepcionalController extends Controller
{
    // 1. El alumno crea la solicitud
    public function subir(Request $request)
    {
        try {
            $resultado = DB::select('CALL INS_CANCE_EXCEP(?, ?, ?)', [
                $request->id_persona,
                $request->prioridad,
                $request->observacion_inicial
            ]);

            return response()->json($resultado[0], 201);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // 2. Para que la coordinadora pueda Ver todas las solicitudes
    public function obtenerTodos()
    {
        try {
            $documentos = Documento::all(); // Trae absolutamente todo de tbl_documento
            return response()->json($documentos, 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // 3. Para qie el alumno pueda ver solo su trámite
    public function obtenerCancelacion($id)
    {
        try {
            $resultado = DB::select('CALL SEL_CANCELACION_EXCEPCIONAL(?)', [$id]);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // 4. ELIMINAR
    public function eliminar(Request $request, $id)
    {
        try {
            $resultado = DB::select('CALL SOFT_DEL_DOC_EXCEPCIONAL(?, ?, ?)', [
                $id,
                $request->id_usuario,
                $request->motivo
            ]);
            return response()->json($resultado[0], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }
}
