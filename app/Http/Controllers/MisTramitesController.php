<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MisTramitesController extends Controller
{
    public function index()
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return redirect()->route('login');
            }

            /*
            |--------------------------------------------------------------------------
            | Resolver id_persona correctamente
            |--------------------------------------------------------------------------
            */
            $idPersona = null;

            if (!empty($usuario->id_persona)) {
                $idPersona = $usuario->id_persona;
            }

            if (!$idPersona && isset($usuario->persona) && $usuario->persona) {
                $idPersona = $usuario->persona->id_persona ?? null;
            }

            if (!$idPersona) {
                $idPersona = session('id_persona')
                    ?? data_get(session('usuario'), 'id_persona')
                    ?? null;
            }

            if (!$idPersona) {
                $idUsuario = $usuario->id_usuario ?? $usuario->id ?? null;

                if ($idUsuario) {
                    $registroUsuario = DB::table('tbl_usuario')
                        ->select('id_persona')
                        ->where('id_usuario', $idUsuario)
                        ->first();

                    $idPersona = $registroUsuario->id_persona ?? null;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Si no encuentra id_persona, abre la vista igual
            |--------------------------------------------------------------------------
            */
            if (!$idPersona) {
                return view('mis_tramites', [
                    'tramites'     => collect(),
                    'total'        => 0,
                    'idPersona'    => null,
                    'mensajeError' => 'No se pudo identificar el id_persona del usuario autenticado.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Consultar trámites
            |--------------------------------------------------------------------------
            */
            $tramites = DB::table('tbl_tramite as t')
                ->leftJoin('tbl_carrera as c', 'c.id_carrera', '=', 't.id_carrera_destino')
                ->select(
                    't.id_tramite',
                    't.tipo_tramite_academico',
                    't.fecha_solicitud',
                    't.resolucion_de_tramite_academico',
                    't.direccion',
                    DB::raw("COALESCE(c.nombre_carrera, 'No aplica') as carrera_destino")
                )
                ->where('t.id_persona', $idPersona)
                ->where('t.estado', 1)
                ->orderByDesc('t.fecha_solicitud')
                ->orderByDesc('t.id_tramite')
                ->get()
                ->map(function ($tramite) {
                    $tipo = trim((string) ($tramite->tipo_tramite_academico ?? ''));
                    $tipo = str_replace('_', ' ', $tipo);
                    $tramite->tipo_tramite_mostrar = mb_convert_case($tipo, MB_CASE_TITLE, 'UTF-8');

                    $estado = trim((string) ($tramite->resolucion_de_tramite_academico ?? ''));
                    $estadoNormalizado = mb_strtolower($estado, 'UTF-8');

                    if (str_contains($estadoNormalizado, 'aprob')) {
                        $tramite->estado_mostrar = 'Aprobada';
                        $tramite->badge_class = 'bg-success';
                    } elseif (str_contains($estadoNormalizado, 'rechaz')) {
                        $tramite->estado_mostrar = 'Rechazada';
                        $tramite->badge_class = 'bg-danger';
                    } elseif (
                        str_contains($estadoNormalizado, 'revision') ||
                        str_contains($estadoNormalizado, 'revisión')
                    ) {
                        $tramite->estado_mostrar = 'Revisión';
                        $tramite->badge_class = 'bg-info text-dark';
                    } else {
                        $tramite->estado_mostrar = 'Pendiente';
                        $tramite->badge_class = 'bg-warning text-dark';
                    }

                    return $tramite;
                });

            return view('mis_tramites', [
                'tramites'     => $tramites,
                'total'        => $tramites->count(),
                'idPersona'    => $idPersona,
                'mensajeError' => null,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error al listar mis trámites', [
                'mensaje' => $e->getMessage(),
                'linea'   => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return view('mis_tramites', [
                'tramites'     => collect(),
                'total'        => 0,
                'idPersona'    => null,
                'mensajeError' => 'Ocurrió un error al cargar la vista de trámites: ' . $e->getMessage(),
            ]);
        }
    }
}