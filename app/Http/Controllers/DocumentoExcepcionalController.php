<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DocumentoExcepcionalController extends Controller
{
    public function index()
    {
        return view('cancelacion');
    }

    public function nuevaSolicitud()
    {
        session()->forget([
            'cancelacion_excepcional.id_tramite',
            'cancelacion_excepcional.motivo_id',
            'cancelacion_excepcional.justificacion',
            'cancelacion_excepcional.causa_justificada',
            'cancelacion_excepcional.paso2_validado',
            'cancelacion_excepcional.id_tramite_validado',
            'show_form',
        ]);

        session()->save();

        return redirect('/cancelacion');
    }

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
                'justificacion.max'      => 'La justificación no puede superar 2000 caracteres.',
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('show_form', true);
        }

        try {
            $user = Auth::user();

            if (!$user || empty($user->id_persona) || empty($user->id_usuario)) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'error' => 'No se pudo identificar al usuario autenticado.',
                    ])
                    ->withInput()
                    ->with('show_form', true);
            }

            $idPersona     = (int) $user->id_persona;
            $idUsuario     = (int) $user->id_usuario;
            $motivoId      = (int) $request->input('motivo_id');
            $justificacion = trim((string) $request->input('justificacion'));
            $prioridad     = $this->mapearMotivoAPrioridad($motivoId);

            $resultado = DB::select('CALL INS_CANCE_EXCEP(?, ?, ?, ?)', [
                $idPersona,
                $prioridad,
                $justificacion,
                $idUsuario,
            ]);

            Log::info('Resultado INS_CANCE_EXCEP', [
                'id_persona' => $idPersona,
                'id_usuario' => $idUsuario,
                'motivo_id'  => $motivoId,
                'prioridad'  => $prioridad,
                'resultado'  => $resultado,
            ]);

            $fila = $resultado[0] ?? null;
            $idTramite = null;

            if ($fila) {
                if (isset($fila->id_tramite_creado) && is_numeric($fila->id_tramite_creado)) {
                    $idTramite = (int) $fila->id_tramite_creado;
                } elseif (isset($fila->id_tramite) && is_numeric($fila->id_tramite)) {
                    $idTramite = (int) $fila->id_tramite;
                } elseif (isset($fila->ID_TRAMITE) && is_numeric($fila->ID_TRAMITE)) {
                    $idTramite = (int) $fila->ID_TRAMITE;
                } elseif (isset($fila->resultado) && is_numeric($fila->resultado)) {
                    $idTramite = (int) $fila->resultado;
                }
            }

            if (!$idTramite) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'error' => 'No fue posible obtener el número de trámite creado.',
                    ])
                    ->withInput()
                    ->with('show_form', true);
            }

            session([
                'cancelacion_excepcional.id_tramite'          => $idTramite,
                'cancelacion_excepcional.motivo_id'           => $motivoId,
                'cancelacion_excepcional.justificacion'       => $justificacion,
                'cancelacion_excepcional.causa_justificada'   => $this->mapearMotivoACausa($motivoId),
                'cancelacion_excepcional.paso2_validado'      => false,
                'cancelacion_excepcional.id_tramite_validado' => null,
            ]);

            return redirect()
                ->route('cancelacion.paso2', ['id_tramite' => $idTramite])
                ->with('success', 'Paso 1 completado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al crear trámite de cancelación excepcional', [
                'mensaje'    => $e->getMessage(),
                'archivo'    => $e->getFile(),
                'linea'      => $e->getLine(),
                'trace'      => $e->getTraceAsString(),
                'id_persona' => Auth::user()->id_persona ?? null,
                'id_usuario' => Auth::user()->id_usuario ?? null,
            ]);

            return redirect()
                ->back()
                ->withErrors([
                    'error' => $this->limpiarMensajeCreacion($e),
                ])
                ->withInput()
                ->with('show_form', true);
        }
    }

    private function mapearMotivoACausa(int $motivoId): string
    {
        return match ($motivoId) {
            1       => 'ENFERMEDAD_ACCIDENTE',
            2       => 'CALAMIDAD_DOMESTICA',
            3       => 'PROBLEMAS_LABORALES',
            default => 'GENERAL',
        };
    }

    /**
     * Debe coincidir con el enum real de tbl_tramite.prioridad:
     * baja | normal | alta
     */
    private function mapearMotivoAPrioridad(int $motivoId): string
    {
        return match ($motivoId) {
            1       => 'alta',   // enfermedad / accidente
            2       => 'alta',   // calamidad
            3       => 'normal', // laboral
            default => 'baja',
        };
    }

    private function limpiarMensajeCreacion(\Throwable $e): string
    {
        $mensaje = $e->getMessage();

        if (str_contains($mensaje, 'Data truncated for column')) {
            return 'No fue posible registrar la solicitud porque uno de los valores no coincide con la estructura de la base de datos.';
        }

        if (str_contains($mensaje, 'No existe un calendario académico activo')) {
            return 'No existe un calendario académico activo para registrar la solicitud.';
        }

        if (str_contains($mensaje, 'SQLSTATE')) {
            return 'Ocurrió un problema al registrar la solicitud. Revise la configuración del procedimiento y la base de datos.';
        }

        if (str_contains($mensaje, 'ERROR:')) {
            return trim(str_replace('ERROR:', '', $mensaje));
        }

        return 'Ocurrió un problema al registrar la solicitud. Intente nuevamente.';
    }
}