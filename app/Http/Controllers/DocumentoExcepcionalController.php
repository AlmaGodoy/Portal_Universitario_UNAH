<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DocumentoExcepcionalController extends Controller
{
    /**
     * Muestra la vista inicial de Cancelación Excepcional (Paso 1).
     */
    public function index()
    {
        return view('cancelacion');
    }

    /**
     * Paso 1:
     * - valida motivo y justificación
     * - crea el trámite por SP
     * - guarda datos mínimos en sesión
     * - redirige al Paso 2
     */
    public function subir(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'motivo_id'     => 'required|integer',
                'justificacion' => 'required|string|min:10|max:2000',
            ],
            [
                'motivo_id.required'     => 'Debe seleccionar un motivo.',
                'motivo_id.integer'      => 'El motivo seleccionado no es válido.',
                'justificacion.required' => 'Debe ingresar una justificación.',
                'justificacion.min'      => 'La justificación debe tener al menos 10 caracteres.',
                'justificacion.max'      => 'La justificación no puede exceder 2000 caracteres.',
            ]
        );

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('show_form', true);
        }

        try {
            $usuario = auth()->user();

            if (!$usuario) {
                return redirect()->route('login')
                    ->withErrors(['error' => 'Debe iniciar sesión para continuar.']);
            }

            $idPersona = $usuario->id_persona ?? null;
            $idUsuario = auth()->id();

            if (!$idPersona || !$idUsuario) {
                return back()
                    ->withErrors(['error' => 'No fue posible identificar el usuario autenticado.'])
                    ->withInput()
                    ->with('show_form', true);
            }

            $resultado = DB::select('CALL INS_CANCE_EXCEP(?, ?, ?, ?)', [
                $idPersona,
                (int) $request->motivo_id,
                trim($request->justificacion),
                $idUsuario,
            ]);

            if (!isset($resultado[0])) {
                return back()
                    ->withErrors(['error' => 'No se recibió respuesta del servidor al crear la solicitud.'])
                    ->withInput()
                    ->with('show_form', true);
            }

            $res = $resultado[0];

            if (($res->resultado ?? null) !== 'OK') {
                return back()
                    ->withErrors([
                        'error' => $res->mensaje ?? 'No fue posible registrar la solicitud.'
                    ])
                    ->withInput()
                    ->with('show_form', true);
            }

            $idTramite = $res->id_tramite_creado ?? null;

            if (!$idTramite) {
                return back()
                    ->withErrors(['error' => 'La solicitud fue creada, pero no se recibió el id del trámite.'])
                    ->withInput()
                    ->with('show_form', true);
            }

            /**
             * Guardamos en sesión para usarlo luego si hace falta.
             * También guardamos el motivo para que el Paso 2 sepa
             * qué documento de respaldo mostrar.
             */
            session([
                'id_tramite' => $idTramite,
                'cancelacion_excepcional.motivo_id' => (int) $request->motivo_id,
                'cancelacion_excepcional.justificacion' => trim($request->justificacion),
                'cancelacion_excepcional.causa_justificada' => $this->mapearMotivo($request->motivo_id),
            ]);

            return redirect()->route('cancelacion.paso2', [
                'id_tramite' => $idTramite
            ])->with('success', 'Solicitud registrada. Ahora adjunte su documentación.');

        } catch (\Throwable $e) {
            return back()
                ->withErrors([
                    'error' => 'Error en el sistema: ' . $e->getMessage()
                ])
                ->withInput()
                ->with('show_form', true);
        }
    }

    /**
     * Convierte el motivo_id del Paso 1 en una clave que el Paso 2 pueda usar.
     * Ajusta este mapeo a los IDs reales de tu BD si fueran distintos.
     */
    private function mapearMotivo($motivoId): ?string
    {
        $motivoId = (int) $motivoId;

        return match ($motivoId) {
            1 => 'ENFERMEDAD_ACCIDENTE',
            2 => 'CALAMIDAD_DOMESTICA',
            3 => 'PROBLEMAS_LABORALES',
            default => null,
        };
    }
}