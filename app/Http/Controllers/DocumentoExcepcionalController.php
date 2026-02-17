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

    // Para que la coordinadora pueda ver todas las solicitudes
    public function obtenerTodos()
    {
        try {
            /** * NOTA: Se utiliza el modelo Documento para traer los datos.
             * El campo 'hash_contenido' está implementado en la DB como ´Index Unique´.
             */
            $documentos = Documento::paginate(20);
            return response()->json($documentos, 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // Guardar el archivo validando la "huella" (Hash) para evitar repetidos
    public function guardarDocumento(Request $request)
    {
        try {
            $nuevoDoc = new Documento();
            $nuevoDoc->id_tramite = $request->id_tramite;
            $nuevoDoc->tipo_documento = $request->tipo_documento;
            $nuevoDoc->nombre_documento = $request->nombre_archivo;
            // La "huella" digital del archivo que evita duplicados
            $nuevoDoc->hash_contenido = $request->hash_contenido;
            $nuevoDoc->ruta_archivo = $request->ruta_archivo;

            $nuevoDoc->save();

            return response()->json(['resultado' => 'OK', 'mensaje' => 'Documento guardado'], 201);

        } catch (QueryException $e) {
            // Si la base de datos detecta que el Hash ya existe
            if ($e->getCode() === '23000') {
                return response()->json([
                    'resultado' => 'REPETIDO',
                    'mensaje' => 'Este archivo ya ha sido registrado previamente.'
                ], 409);
            }
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
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



    // Eliminar (Borrado lógico)
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
