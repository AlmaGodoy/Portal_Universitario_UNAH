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
     * Muestra el formulario principal (Paso 1 + Pantalla intro)
     */
    public function index()
    {
        return view('cancelacion');
    }

    /**
     * Paso 1: Crear la solicitud de cancelación
     * Recibe motivo_id + justificacion, llama al SP y redirige al Paso 2
     */
    public function subir(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'motivo_id'     => 'required|integer',
            'justificacion' => 'required|string|min:10',
        ]);

        // Si falla validación: regresa al formulario Y mantiene el step-form visible
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('show_form', true); // ← CLAVE: le dice a la vista que muestre paso 2
        }

        try {
            // Ajusta estos valores a tu sistema de autenticación
            $id_persona = auth()->user()->id_persona ?? 8;
            $id_usuario = auth()->id() ?? 4;

            $resultado = DB::select('CALL INS_CANCE_EXCEP(?, ?, ?, ?)', [
                $id_persona,
                $request->motivo_id,      // → p_prioridad   (ID del motivo)
                $request->justificacion,  // → p_observacion_inicial
                $id_usuario
            ]);

            if (isset($resultado[0])) {
                $res = $resultado[0];

                if ($res->resultado === 'OK') {
                    // Éxito: redirige al Paso 2 pasando el id_tramite por sesión
                    // SP retorna el campo como 'id_tramite_creado'
                    return redirect()->route('cancelacion.paso2')
                        ->with('id_tramite', $res->id_tramite_creado ?? null)
                        ->with('success', 'Solicitud registrada. Ahora adjunte su documentación.');
                }

                // SP respondió con error de negocio
                return back()
                    ->withErrors(['error' => $res->mensaje])
                    ->withInput()
                    ->with('show_form', true);
            }

            return back()
                ->withErrors(['error' => 'No se recibió respuesta del servidor.'])
                ->withInput()
                ->with('show_form', true);

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Error en el sistema: ' . $e->getMessage()])
                ->withInput()
                ->with('show_form', true);
        }
    }

    /**
     * Muestra el formulario del Paso 2 (subir documentos)
     */
    public function paso2()
    {
        // Si no viene id_tramite en sesión, no puede estar aquí
        if (!session('id_tramite')) {
            return redirect()->route('cancelacion.index')
                ->withErrors(['error' => 'Debe completar el Paso 1 primero.']);
        }

        return view('cancelacion_paso2', [
            'id_tramite' => session('id_tramite')
        ]);
    }

    /**
     * Paso 2: Guardar el archivo PDF
     */
    public function guardarDocumento(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_tramite'  => 'required|integer',
            'archivo_pdf' => 'required|file|mimes:pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors(['error' => 'El archivo debe ser un PDF menor a 10MB.'])->withInput();
        }

        try {
            DB::beginTransaction();

            $archivo       = $request->file('archivo_pdf');
            $nombreArchivo = 'PUMA_' . time() . '_' . $archivo->getClientOriginalName();
            $ruta          = $archivo->storeAs('public/cancelaciones', $nombreArchivo);

            $nuevoDoc = new Documento();
            $nuevoDoc->id_tramite       = $request->id_tramite;
            $nuevoDoc->tipo_documento   = 'SOLICITUD_CANCELACION';
            $nuevoDoc->nombre_documento = $nombreArchivo;
            $nuevoDoc->hash_contenido   = hash_file('sha256', $archivo->getRealPath());
            $nuevoDoc->ruta_archivo     = $ruta;
            $nuevoDoc->numero_folio     = 1;
            $nuevoDoc->estado           = 1;
            $nuevoDoc->save();

            DB::statement('CALL VAL_TRAMITE_ANTIFRAUDE(?)', [$request->id_tramite]);

            DB::commit();

            return redirect()->route('cancelacion.exito')
                ->with('mensaje', 'Solicitud enviada correctamente a la facultad.');

        } catch (QueryException $e) {
            DB::rollBack();
            $mensaje = ($e->errorInfo[1] == 1062)
                ? 'Este documento ya fue subido anteriormente.'
                : $e->getMessage();
            return back()->withErrors(['error' => $mensaje])->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al guardar: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Vista de éxito final
     */
    public function exito()
    {
        return view('cancelacion_exito', [
            'mensaje' => session('mensaje', 'Solicitud procesada correctamente.')
        ]);
    }
}
