<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Documento;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class DocumentoExcepcionalController extends Controller
{
    /**
     * Paso 1: Crear la solicitud de cancelación
     */
    public function subir(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prioridad'           => 'required|string|max:20',
            'observacion_inicial' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'Datos incompletos',
                'errores'   => $validator->errors()
            ], 400);
        }

        try {
            // Valores de prueba
            $id_persona_prueba = 8;
            $id_usuario_prueba = 4;

            // Ejecutar el procedimiento almacenado
            // El orden debe ser: p_id_persona, p_prioridad, p_observacion_inicial, p_id_usuario
            $resultado = DB::select('CALL INS_CANCE_EXCEP(?, ?, ?, ?)', [
                $id_persona_prueba,
                $request->prioridad,
                $request->observacion_inicial,
                $id_usuario_prueba
            ]);

            // Verificamos si el procedimiento devolvió algo
            if (isset($resultado[0])) {
                $res = $resultado[0];

                // Si el procedimiento devolvió 'OK', respondemos con el ID creado
                if ($res->resultado === 'OK') {
                    return response()->json($res, 201);
                }

                return response()->json($res, 400);
            }

            return response()->json(['resultado' => 'ERROR', 'mensaje' => 'No se recibió respuesta del servidor'], 500);

        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'Error al ejecutar procedimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Paso 2: Guardar el archivo PDF
     */
    public function guardarDocumento(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_tramite'  => 'required|integer',
            'archivo_pdf' => 'required|file|mimes:pdf|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json(['resultado' => 'ERROR', 'mensaje' => 'Datos o archivo inválidos'], 400);
        }

        try {
            DB::beginTransaction();

            $archivo = $request->file('archivo_pdf');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();

            $ruta = $archivo->storeAs('public/cancelaciones', $nombreArchivo);

            $nuevoDoc = new Documento();
            $nuevoDoc->id_tramite      = $request->id_tramite;
            $nuevoDoc->tipo_documento  = 'CONSTANCIA_EXCEPCIONAL';
            $nuevoDoc->nombre_documento = $nombreArchivo;
            $nuevoDoc->hash_contenido  = hash_file('sha256', $archivo->getRealPath());
            $nuevoDoc->ruta_archivo    = $ruta;
            $nuevoDoc->numero_folio    = $request->numero_folio ?? 1;
            $nuevoDoc->estado          = 1;
            $nuevoDoc->save();

            // Llamada al segundo procedimiento de validación
            DB::statement('CALL VAL_TRAMITE_ANTIFRAUDE(?)', [$request->id_tramite]);

            DB::commit();

            return response()->json([
                'resultado'  => 'OK',
                'mensaje'    => 'Documento guardado y validado con éxito.',
                'id_tramite' => $request->id_tramite
            ], 201);

        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->errorInfo[1] == 1062) {
                return response()->json(['resultado' => 'REPETIDO', 'mensaje' => 'Este documento ya existe (Hash duplicado).'], 409);
            }
            return response()->json(['resultado' => 'ERROR_DB', 'mensaje' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['resultado' => 'ERROR', 'mensaje' => $e->getMessage()], 500);
        }
    }
}
