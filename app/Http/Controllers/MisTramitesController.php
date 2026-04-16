<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MisTramitesController extends Controller
{
    /**
     * =========================================================
     * VISTA PRINCIPAL
     * =========================================================
     * Carga la página "Mis trámites" con los datos ya listos
     * para mostrarse en Blade.
     */
    public function index()
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return redirect()->route('login');
            }

            // Resolver el id_persona del usuario autenticado
            $idPersona = $this->resolverIdPersona($usuario);

            /*
            |--------------------------------------------------------------------------
            | Si no se encuentra el id_persona
            |--------------------------------------------------------------------------
            | Se abre la vista igual, pero mostrando colección vacía y mensaje.
            */
            if (!$idPersona) {
                return view('mis_tramites', [
                    'tramites'     => collect(),
                    'total'        => 0,
                    'idPersona'    => null,
                    'mensajeError' => 'No se pudo identificar el id_persona del usuario autenticado.',
                ]);
            }

            // Obtener trámites normalizados
            $tramites = $this->obtenerTramitesPorPersona($idPersona);

            return view('mis_tramites', [
                'tramites'     => $tramites,
                'total'        => $tramites->count(),
                'idPersona'    => $idPersona,
                'mensajeError' => null,
            ]);

        } catch (Throwable $e) {
            Log::error('Error al cargar la vista de mis trámites', [
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

    /**
     * =========================================================
     * API JSON
     * =========================================================
     * Este método es el que te estaba faltando.
     * Lo usa el JS cuando hace fetch a /api/mis-tramites/listado
     */
    public function listadoJson(): JsonResponse
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return response()->json([
                    'ok'        => false,
                    'resultado' => [],
                    'message'   => 'Usuario no autenticado.',
                ], 401);
            }

            // Resolver el id_persona igual que en index()
            $idPersona = $this->resolverIdPersona($usuario);

            if (!$idPersona) {
                return response()->json([
                    'ok'        => false,
                    'resultado' => [],
                    'message'   => 'No se pudo identificar el id_persona del usuario autenticado.',
                ], 422);
            }

            // Obtener trámites normalizados
            $tramites = $this->obtenerTramitesPorPersona($idPersona);

            /*
            |--------------------------------------------------------------------------
            | Respuesta JSON
            |--------------------------------------------------------------------------
            | Dejamos varias llaves para compatibilidad con distintos JS:
            | - ok
            | - resultado
            | - tramites
            | - total
            | - message
            */
            return response()->json([
                'ok'        => true,
                'resultado' => $tramites->values(),
                'tramites'  => $tramites->values(),
                'total'     => $tramites->count(),
                'message'   => 'Listado obtenido correctamente.',
            ]);

        } catch (Throwable $e) {
            Log::error('Error al obtener listado JSON de mis trámites', [
                'mensaje' => $e->getMessage(),
                'linea'   => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json([
                'ok'        => false,
                'resultado' => [],
                'tramites'  => [],
                'total'     => 0,
                'message'   => 'Ocurrió un error al obtener los trámites: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================================================
     * RESOLVER ID_PERSONA
     * =========================================================
     * Centralizamos aquí toda la lógica para encontrar el id_persona.
     */
    private function resolverIdPersona($usuario): ?int
    {
        $idPersona = null;

        // 1. Directamente desde el usuario autenticado
        if (!empty($usuario->id_persona)) {
            $idPersona = (int) $usuario->id_persona;
        }

        // 2. Desde relación persona
        if (!$idPersona && isset($usuario->persona) && $usuario->persona) {
            $idPersona = $usuario->persona->id_persona ?? null;
            $idPersona = $idPersona ? (int) $idPersona : null;
        }

        // 3. Desde sesión
        if (!$idPersona) {
            $idPersona = session('id_persona')
                ?? data_get(session('usuario'), 'id_persona')
                ?? null;

            $idPersona = $idPersona ? (int) $idPersona : null;
        }

        // 4. Desde tbl_usuario
        if (!$idPersona) {
            $idUsuario = $usuario->id_usuario ?? $usuario->id ?? null;

            if ($idUsuario) {
                $registroUsuario = DB::table('tbl_usuario')
                    ->select('id_persona')
                    ->where('id_usuario', $idUsuario)
                    ->first();

                $idPersona = $registroUsuario->id_persona ?? null;
                $idPersona = $idPersona ? (int) $idPersona : null;
            }
        }

        return $idPersona ?: null;
    }

    /**
     * =========================================================
     * OBTENER TRÁMITES POR PERSONA
     * =========================================================
     * Ejecuta la consulta y deja cada trámite listo para la vista y el JSON.
     */
    private function obtenerTramitesPorPersona(int $idPersona): Collection
    {
        return DB::table('tbl_tramite as t')
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
                /*
                |--------------------------------------------------------------------------
                | Tipo de trámite para mostrar
                |--------------------------------------------------------------------------
                */
                $tipo = trim((string) ($tramite->tipo_tramite_academico ?? ''));
                $tipo = str_replace('_', ' ', $tipo);
                $tramite->tipo_tramite_mostrar = mb_convert_case($tipo, MB_CASE_TITLE, 'UTF-8');

                /*
                |--------------------------------------------------------------------------
                | Estado visual / badge
                |--------------------------------------------------------------------------
                */
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

                /*
                |--------------------------------------------------------------------------
                | Campos extra para compatibilidad con el frontend
                |--------------------------------------------------------------------------
                */
                $tramite->numero_tramite = $tramite->id_tramite;
                $tramite->tipo = $tramite->tipo_tramite_mostrar;
                $tramite->fecha = $tramite->fecha_solicitud;
                $tramite->detalle_clave = !empty($tramite->direccion)
                    ? $tramite->direccion
                    : 'Trámite académico registrado';
                $tramite->estado = $tramite->estado_mostrar;

                return $tramite;
            });
    }
}