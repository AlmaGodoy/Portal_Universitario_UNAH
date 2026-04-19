<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Throwable;

class DocumentoController extends Controller
{
    
    public function crear(Request $request)
    {
        $request->validate([
            'id_tramite'     => 'required|integer',
            'tipo_documento' => 'required|string|max:50',
            'archivo'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        try {
            $archivo = $request->file('archivo');

            // Guardar archivo en storage/app/public/documentos
            $ruta = $archivo->store('documentos', 'public');

            $nombreArchivo = $archivo->getClientOriginalName();
            $hash = hash_file('sha256', $archivo->getRealPath());

            $data = DB::select('CALL INS_SUBIR_DOCUMENTO(?, ?, ?, ?, ?)', [
                $request->id_tramite,
                $request->tipo_documento,
                $nombreArchivo, // nombre_documento
                $hash,
                $ruta
            ]);

            return response()->json($data[0] ?? $data, 201);

        } catch (Throwable $e) {
           
            if (!empty($ruta) && Storage::disk('public')->exists($ruta)) {
                Storage::disk('public')->delete($ruta);
            }

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $this->obtenerMensajeLimpio(
                    $e,
                    'No fue posible subir el documento.'
                )
            ], $this->obtenerCodigoHttp($e, 500));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VER DOCUMENTOS POR TRÁMITE
    |--------------------------------------------------------------------------
    */
    public function ver($id_tramite)
    {
        try {
            $data = DB::select('CALL SEL_SUBIR_DOCUMENTO(?)', [$id_tramite]);

            return response()->json($data, 200);

        } catch (Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $this->obtenerMensajeLimpio(
                    $e,
                    'No fue posible consultar los documentos del trámite.'
                )
            ], $this->obtenerCodigoHttp($e, 500));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR DOCUMENTO
    |--------------------------------------------------------------------------
    */
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

        } catch (Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $this->obtenerMensajeLimpio(
                    $e,
                    'No fue posible actualizar el documento.'
                )
            ], $this->obtenerCodigoHttp($e, 500));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR DOCUMENTO (SOFT DELETE)
    |--------------------------------------------------------------------------
    */
    public function eliminar($id_documento)
    {
        try {
            $data = DB::select('CALL SOFT_DEL_SUBIR_DOCUMENTO(?)', [$id_documento]);

            return response()->json($data[0] ?? $data, 200);

        } catch (Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $this->obtenerMensajeLimpio(
                    $e,
                    'No fue posible eliminar el documento.'
                )
            ], $this->obtenerCodigoHttp($e, 500));
        }
    }

   
    private function obtenerMensajeLimpio(Throwable $e, string $mensajeGenerico): string
    {
        if ($e instanceof QueryException) {
            $mensajeBD = trim((string) ($e->errorInfo[2] ?? ''));

            if ($mensajeBD !== '') {
                // Quita prefijos numéricos como "1644 "
                $mensajeBD = preg_replace('/^\d+\s*/', '', $mensajeBD);

                // Quita "SQLSTATE[45000]: <<Unknown error>>:"
                $mensajeBD = preg_replace('/^SQLSTATE\[[^\]]+\]:\s*<<[^>]+>>:\s*/i', '', $mensajeBD);

                // Quita "(Connection: mysql ...)"
                $mensajeBD = preg_replace('/\s*\(Connection:.*$/u', '', $mensajeBD);

                // Quita "SQL: CALL ..."
                $mensajeBD = preg_replace('/\s*SQL:\s.*$/iu', '', $mensajeBD);

                return trim($mensajeBD);
            }
        }

        $mensajeCompleto = trim($e->getMessage());

        if ($mensajeCompleto !== '') {
            // Extrae el mensaje útil si Laravel lo trae envuelto
            if (preg_match('/:\s*\d+\s+(.*?)(?:\s+\(Connection:|\s+SQL:|$)/u', $mensajeCompleto, $coincidencias)) {
                return trim($coincidencias[1]);
            }

            // Limpiezas adicionales
            $mensajeCompleto = preg_replace('/^SQLSTATE\[[^\]]+\]:\s*<<[^>]+>>:\s*/i', '', $mensajeCompleto);
            $mensajeCompleto = preg_replace('/\s*\(Connection:.*$/u', '', $mensajeCompleto);
            $mensajeCompleto = preg_replace('/\s*SQL:\s.*$/iu', '', $mensajeCompleto);

            return trim($mensajeCompleto);
        }

        return $mensajeGenerico;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: MAPEAR ERRORES MYSQL A HTTP
    |--------------------------------------------------------------------------
    */
    private function obtenerCodigoHttp(Throwable $e, int $codigoPorDefecto = 500): int
    {
        if ($e instanceof QueryException) {
            $codigoMysql = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;

            // SIGNAL SQLSTATE '45000' en MySQL suele devolver 1644
            if ($codigoMysql === 1644) {
                return 422;
            }
        }

        return $codigoPorDefecto;
    }
}