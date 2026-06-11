<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class BitacoraController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'No autorizado.');
        }

        $rol = (int) ($user->id_rol ?? 0);

        switch ($rol) {
            case 4:
                return redirect()->route('bitacora.coordinador');
            case 5:
                return redirect()->route('bitacora.secretaria_general');
            case 1:
                return redirect()->route('bitacora.secretaria_academica');
            case 3:
                return redirect()->route('bitacora.secretaria_general');
            default:
                abort(403, 'No autorizado para acceder a bitácora.');
        }
    }

    public function coordinador(Request $request)
    {
        $bitacoras = $this->consultarBitacoraCoordinador($request);
        return view('bitacora_coordinador', compact('bitacoras'));
    }

    public function secretariaCarrera(Request $request)
    {
        $bitacoras = $this->consultarBitacoraPorSP($request);
        return view('bitacora_secretaria_general', compact('bitacoras'));
    }

    public function secretariaAcademica(Request $request)
    {
        $bitacoras = $this->consultarBitacoraPorSP($request);
        return view('bitacora_secretaria_academica', compact('bitacoras'));
    }

    public function secretariaGeneral(Request $request)
    {
        $carreras = DB::table('tbl_carrera')->where('estado', 1)->get();
        $bitacoras = $this->consultarBitacoraPorSP($request);

        return view('bitacora_secretaria_general', compact('bitacoras', 'carreras'));
    }

    private function consultarBitacoraCoordinador(Request $request): LengthAwarePaginator
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'No autorizado.');
        }

        $fecha_inicio = $request->filled('fecha_inicio')
            ? $request->fecha_inicio
            : now()->subMonths(6)->toDateString();

        $fecha_fin = $request->filled('fecha_fin')
            ? $request->fecha_fin
            : now()->toDateString();

        $id_carrera = $user->id_carrera ?? null;

        $query = DB::table('tbl_tramite as t')
            ->leftJoin('tbl_persona as p', 'p.id_persona', '=', 't.id_persona')
            ->selectRaw("
                t.id_tramite AS id,
                COALESCE(p.nombre_persona, 'Sin estudiante') AS estudiante,
                CASE
                    WHEN t.tipo_tramite_academico = 'cancelacion' THEN 'Cancelación de clases'
                    WHEN t.tipo_tramite_academico = 'cambio_carrera' THEN 'Cambio de carrera'
                    WHEN t.tipo_tramite_academico = 'reposicion' THEN 'Reposición'
                    ELSE COALESCE(t.tipo_tramite_academico, 'Trámite')
                END AS tramite,
                'Registro de trámite' AS accion,
                CONCAT(
                    'Se registró trámite con estado: ',
                    COALESCE(t.resolucion_de_tramite_academico, 'pendiente')
                ) AS descripcion,
                t.fecha_solicitud AS fecha
            ")
            ->where('t.estado', 1)
            ->whereDate('t.fecha_solicitud', '>=', $fecha_inicio)
            ->whereDate('t.fecha_solicitud', '<=', $fecha_fin);

        if (!empty($id_carrera)) {
            $query->where(function ($q) use ($id_carrera) {
                $q->where('t.id_carrera_destino', $id_carrera);

                if (Schema::hasColumn('tbl_tramite', 'id_carrera')) {
                    $q->orWhere('t.id_carrera', $id_carrera);
                }
            });
        }

        $resultados = $query
            ->orderByDesc('t.fecha_solicitud')
            ->get()
            ->toArray();

        return $this->paginateResults($resultados, $request, 10);
    }

    private function consultarBitacoraPorSP(Request $request): LengthAwarePaginator
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'No autorizado.');
        }

        $fecha_inicio = $request->filled('fecha_inicio')
            ? $request->fecha_inicio
            : now()->subMonth()->toDateString();

        $fecha_fin = $request->filled('fecha_fin')
            ? $request->fecha_fin
            : now()->toDateString();

        $id_usuario = $user->id_usuario ?? $user->id ?? null;
        $id_rol     = (int) ($user->id_rol ?? 0);
        $id_carrera = $request->filled('id_carrera')
            ? $request->id_carrera
            : ($user->id_carrera ?? null);

        $resultados = DB::select(
            'CALL SEL_BITACORA_TRAMITE(?,?,?,?,?)',
            [
                $fecha_inicio,
                $fecha_fin,
                $id_usuario,
                $id_rol,
                $id_carrera
            ]
        );

        return $this->paginateResults($resultados, $request, 10);
    }

    private function paginateResults(array $resultados, Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $page = (int) $request->get('page', 1);

        return new LengthAwarePaginator(
            array_slice($resultados, ($page - 1) * $perPage, $perPage),
            count($resultados),
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
