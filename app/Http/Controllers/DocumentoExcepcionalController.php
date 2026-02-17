<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Documento;
use Illuminate\Database\QueryException;

class DocumentoExcepcionalController extends Controller
{
    // El alumno crea su solicitud
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

    /**
     * OBTENER TODOS (Con Soft Delete)
     */
    public function obtenerTodos()
    {
        try {
            // Filtro: Solo documentos activos (estado = 1)
            $documentos = Documento::where('estado', 1)->paginate(20);
            return response()->json($documentos, 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // Guardar el archivo validando la "huella" (Hash)
    public function guardarDocumento(Request $request)
    {
        try {
            $nuevoDoc = new Documento();
            $nuevoDoc->id_tramite = $request->id_tramite;
            $nuevoDoc->tipo_documento = $request->tipo_documento;
            $nuevoDoc->nombre_documento = $request->nombre_archivo; // Asegurado con tu JSON de Postman
            $nuevoDoc->hash_contenido = $request->hash_contenido;
            $nuevoDoc->ruta_archivo = $request->ruta_archivo;
            $nuevoDoc->estado = 1; // Por defecto activo al crear

            $nuevoDoc->save();

            return response()->json(['resultado' => 'OK', 'mensaje' => 'Documento guardado'], 201);

        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1];

            if ($errorCode == 1062) {
                return response()->json([
                    'resultado' => 'REPETIDO',
                    'mensaje' => 'La huella (hash) de este archivo ya existe en el sistema.'
                ], 409);
            }

            if ($errorCode == 1452) {
                return response()->json([
                    'resultado' => 'ERROR_RELACION',
                    'mensaje' => 'El ID de trámite ' . $request->id_tramite . ' no existe en la base de datos.'
                ], 400);
            }

            // Para cualquier otro error de base de datos, mostramos el mensaje real
            return response()->json([
                'resultado' => 'ERROR_SQL',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    // Para que el alumno pueda ver solo su trámite
    public function obtenerCancelacion($id)
    {
        try {
            $resultado = DB::select('CALL SEL_CANCELACION_EXCEPCIONAL(?)', [$id]);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // Método para actualizar los datos
    public function actualizar(Request $request, $id)
    {
        try {
            $documento = Documento::find($id);

            if (!$documento) {
                return response()->json(['resultado' => 'ERROR', 'mensaje' => 'No existe el documento con ID: ' . $id], 404);
            }

            $documento->tipo_documento = $request->tipo_documento;
            $documento->nombre_documento = $request->nombre_archivo;
            $documento->save();

            return response()->json(['resultado' => 'OK', 'mensaje' => 'Actualizado correctamente'], 200);

        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    /**
     * ELIMINAR (Borrado lógico vía Procedimiento Almacenado corregido)
     */
    public function eliminar(Request $request, $id)
    {
        try {

            $resultado = DB::select('CALL SOFT_DEL_DOC_EXCEPCIONAL(?, ?, ?)', [
                $id,
                $request->id_usuario,
                $request->motivo
            ]);

            return response()->json([
                'resultado' => 'OK',
                'mensaje' => 'Documento eliminado lógicamente',
                'detalle' => $resultado[0]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }
}
