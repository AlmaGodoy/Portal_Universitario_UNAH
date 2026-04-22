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
            'cancelacion_excepcional.tipo_cancelacion',
            'cancelacion_excepcional.tipo_cancelacion_label',
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
                'motivo_id'        => 'required|integer',
                'tipo_cancelacion' => 'required|string|in:parcial,total',
                'justificacion'    => 'required|string|min:10|max:2000',
            ],
            [
                'motivo_id.required'         => 'Debe seleccionar un motivo.',
                'motivo_id.integer'          => 'El motivo seleccionado no es válido.',
                'tipo_cancelacion.required'  => 'Debe seleccionar el tipo de cancelación.',
                'tipo_cancelacion.in'        => 'El tipo de cancelación seleccionado no es válido.',
                'justificacion.required'     => 'Debe ingresar una justificación.',
                'justificacion.min'          => 'La justificación debe tener al menos 10 caracteres.',
                'justificacion.max'          => 'La justificación no puede superar 2000 caracteres.',
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

            $idPersona        = (int) $user->id_persona;
            $idUsuario        = (int) $user->id_usuario;
            $motivoId         = (int) $request->input('motivo_id');
            $tipoCancelacion  = strtolower(trim((string) $request->input('tipo_cancelacion')));
            $justificacion    = trim((string) $request->input('justificacion'));
            $prioridad        = $this->mapearMotivoAPrioridad($motivoId);

            $resultado = DB::select('CALL INS_CANCE_EXCEP(?, ?, ?, ?)', [
                $idPersona,
                $prioridad,
                $justificacion,
                $idUsuario,
            ]);

            Log::info('Resultado INS_CANCE_EXCEP', [
                'resultado'        => $resultado,
                'tipo_cancelacion' => $tipoCancelacion,
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
                $tramiteReciente = DB::table('tbl_tramite')
                    ->where('id_persona', $idPersona)
                    ->orderByDesc('id_tramite')
                    ->first();

                if ($tramiteReciente && !empty($tramiteReciente->id_tramite)) {
                    $idTramite = (int) $tramiteReciente->id_tramite;
                }
            }

            if (!$idTramite) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'error' => 'El procedimiento INS_CANCE_EXCEP sí ejecutó, pero no devolvió ni permitió localizar un id_tramite válido.',
                    ])
                    ->withInput()
                    ->with('show_form', true);
            }

            session([
                'cancelacion_excepcional.id_tramite'          => $idTramite,
                'cancelacion_excepcional.motivo_id'           => $motivoId,
                'cancelacion_excepcional.justificacion'       => $justificacion,
                'cancelacion_excepcional.causa_justificada'   => $this->mapearMotivoACausa($motivoId),
                'cancelacion_excepcional.tipo_cancelacion'    => $tipoCancelacion,
                'cancelacion_excepcional.tipo_cancelacion_label' => $this->mapearTipoCancelacionALabel($tipoCancelacion),
                'cancelacion_excepcional.paso2_validado'      => false,
                'cancelacion_excepcional.id_tramite_validado' => null,
            ]);

            return redirect()
                ->route('cancelacion.paso2', ['id_tramite' => $idTramite])
                ->with('success', 'Paso 1 completado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al crear trámite de cancelación excepcional', [
                'mensaje'          => $e->getMessage(),
                'archivo'          => $e->getFile(),
                'linea'            => $e->getLine(),
                'tipo_cancelacion' => $request->input('tipo_cancelacion'),
            ]);

            return redirect()
                ->back()
                ->withErrors([
                    'error' => 'Error real: ' . $e->getMessage(),
                ])
                ->withInput()
                ->with('show_form', true);
        }
    }

    private function mapearMotivoACausa(int $motivoId): string
    {
        return match ($motivoId) {
            1 => 'ENFERMEDAD_ACCIDENTE',
            2 => 'CALAMIDAD_DOMESTICA',
            3 => 'PROBLEMAS_LABORALES',
            default => 'GENERAL',
        };
    }

    private function mapearMotivoAPrioridad(int $motivoId): string
    {
        return match ($motivoId) {
            1 => 'alta',
            2 => 'alta',
            3 => 'normal',
            default => 'baja',
        };
    }

    private function mapearTipoCancelacionALabel(string $tipoCancelacion): string
    {
        return match (strtolower($tipoCancelacion)) {
            'parcial' => 'Parcial',
            'total'   => 'Total',
            default   => 'No definido',
        };
    }
}
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
            'cancelacion_excepcional.tipo_cancelacion',
            'cancelacion_excepcional.tipo_cancelacion_label',
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
                'motivo_id'        => 'required|integer',
                'tipo_cancelacion' => 'required|string|in:parcial,total',
                'justificacion'    => 'required|string|min:10|max:2000',
            ],
            [
                'motivo_id.required'         => 'Debe seleccionar un motivo.',
                'motivo_id.integer'          => 'El motivo seleccionado no es válido.',
                'tipo_cancelacion.required'  => 'Debe seleccionar el tipo de cancelación.',
                'tipo_cancelacion.in'        => 'El tipo de cancelación seleccionado no es válido.',
                'justificacion.required'     => 'Debe ingresar una justificación.',
                'justificacion.min'          => 'La justificación debe tener al menos 10 caracteres.',
                'justificacion.max'          => 'La justificación no puede superar 2000 caracteres.',
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

            $idPersona        = (int) $user->id_persona;
            $idUsuario        = (int) $user->id_usuario;
            $motivoId         = (int) $request->input('motivo_id');
            $tipoCancelacion  = strtolower(trim((string) $request->input('tipo_cancelacion')));
            $justificacion    = trim((string) $request->input('justificacion'));
            $prioridad        = $this->mapearMotivoAPrioridad($motivoId);

            $resultado = DB::select('CALL INS_CANCE_EXCEP(?, ?, ?, ?)', [
                $idPersona,
                $prioridad,
                $justificacion,
                $idUsuario,
            ]);

            Log::info('Resultado INS_CANCE_EXCEP', [
                'resultado'        => $resultado,
                'tipo_cancelacion' => $tipoCancelacion,
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
                $tramiteReciente = DB::table('tbl_tramite')
                    ->where('id_persona', $idPersona)
                    ->orderByDesc('id_tramite')
                    ->first();

                if ($tramiteReciente && !empty($tramiteReciente->id_tramite)) {
                    $idTramite = (int) $tramiteReciente->id_tramite;
                }
            }

            if (!$idTramite) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'error' => 'El procedimiento INS_CANCE_EXCEP sí ejecutó, pero no devolvió ni permitió localizar un id_tramite válido.',
                    ])
                    ->withInput()
                    ->with('show_form', true);
            }

            session([
                'cancelacion_excepcional.id_tramite'          => $idTramite,
                'cancelacion_excepcional.motivo_id'           => $motivoId,
                'cancelacion_excepcional.justificacion'       => $justificacion,
                'cancelacion_excepcional.causa_justificada'   => $this->mapearMotivoACausa($motivoId),
                'cancelacion_excepcional.tipo_cancelacion'    => $tipoCancelacion,
                'cancelacion_excepcional.tipo_cancelacion_label' => $this->mapearTipoCancelacionALabel($tipoCancelacion),
                'cancelacion_excepcional.paso2_validado'      => false,
                'cancelacion_excepcional.id_tramite_validado' => null,
            ]);

            return redirect()
                ->route('cancelacion.paso2', ['id_tramite' => $idTramite])
                ->with('success', 'Paso 1 completado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al crear trámite de cancelación excepcional', [
                'mensaje'          => $e->getMessage(),
                'archivo'          => $e->getFile(),
                'linea'            => $e->getLine(),
                'tipo_cancelacion' => $request->input('tipo_cancelacion'),
            ]);

            return redirect()
                ->back()
                ->withErrors([
                    'error' => 'Error real: ' . $e->getMessage(),
                ])
                ->withInput()
                ->with('show_form', true);
        }
    }

    private function mapearMotivoACausa(int $motivoId): string
    {
        return match ($motivoId) {
            1 => 'ENFERMEDAD_ACCIDENTE',
            2 => 'CALAMIDAD_DOMESTICA',
            3 => 'PROBLEMAS_LABORALES',
            default => 'GENERAL',
        };
    }

    private function mapearMotivoAPrioridad(int $motivoId): string
    {
        return match ($motivoId) {
            1 => 'alta',
            2 => 'alta',
            3 => 'normal',
            default => 'baja',
        };
    }

    private function mapearTipoCancelacionALabel(string $tipoCancelacion): string
    {
        return match (strtolower($tipoCancelacion)) {
            'parcial' => 'Parcial',
            'total'   => 'Total',
            default   => 'No definido',
        };
    }
}
