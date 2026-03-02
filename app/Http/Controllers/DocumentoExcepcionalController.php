<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Documento;
use Illuminate\Database\QueryException;

class DocumentoExcepcionalController extends Controller
{
    // El alumno crea su solicitud inicial
    public function subir(Request $request)
    {
        try {
            $id_persona_autenticada = auth()->user()->id_persona; //Para traer el ID automático de la persona logueada .
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
     * OBTENER TODOS
     */
    public function obtenerTodos()
    {
        try {
            $documentos = Documento::where('estado', 1)->paginate(20);
            return response()->json($documentos, 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // 🔹 Guardar archivo INTEGRANDO el procedimiento Antifraude
    public function guardarDocumento(Request $request)
    {
        try {
            // Iniciamos transacción: si algo falla, no se guarda nada en la BD
            DB::beginTransaction();

            $nuevoDoc = new Documento();
            $nuevoDoc->id_tramite = $request->id_tramite;
            $nuevoDoc->tipo_documento = $request->tipo_documento;
            $nuevoDoc->nombre_documento = $request->nombre_archivo;
            $nuevoDoc->hash_contenido = $request->hash_contenido;
            $nuevoDoc->ruta_archivo = $request->ruta_archivo;

            //Añadimos numero_folio para que el PROC de fraude pueda comparar
            $nuevoDoc->numero_folio = $request->numero_folio;

            $nuevoDoc->estado = 1;
            $nuevoDoc->save();

            // 🔥 LLAMADA AL PROCEDIMIENTO ANTIFRAUDE

            DB::statement('CALL VAL_TRAMITE_ANTIFRAUDE(?)', [$request->id_tramite]);

            // Si llegamos aquí, todo está bien
            DB::commit();

            return response()->json(['resultado' => 'OK', 'mensaje' => 'Documento guardado y validado sin fraude'], 201);

        } catch (QueryException $e) {
            DB::rollBack(); // Deshacer el guardado del documento si hubo error

            $errorCode = $e->errorInfo[1];

            if ($errorCode == 1062) {
                return response()->json(['resultado' => 'REPETIDO', 'mensaje' => 'La huella (hash) ya existe.'], 409);
            }

            if ($errorCode == 1452) {
                return response()->json(['resultado' => 'ERROR_RELACION', 'mensaje' => 'El ID de trámite no existe.'], 400);
            }

            // Captura los mensajes 'ALERTA DE FRAUDE' o 'TRÁMITE PROCESADO' de tu procedimiento
            return response()->json([
                'resultado' => 'ERROR_VALIDACION',
                'mensaje' => $e->getMessage()
            ], 400);

        } catch (\Exception $e) {
            DB::rollBack();
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

    // Actualizar datos básicos
    public function actualizar(Request $request, $id)
    {
        try {
            $documento = Documento::find($id);
            if (!$documento) {
                return response()->json(['resultado' => 'ERROR', 'mensaje' => 'ID no encontrado'], 404);
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
            return response()->json(['resultado' => 'OK', 'mensaje' => 'Documento eliminado', 'detalle' => $resultado[0]], 200);
        } catch (\Exception $e) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }
}
