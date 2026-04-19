<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class BitacoraController extends Controller
{
    public function index(Request $request)
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
                return redirect()->route('bitacora.secretaria_academica');

            case 1:
                return redirect()->route('bitacora.secretaria_general');

            default:
                abort(403, 'No autorizado para acceder a bitácora.');
        }
    }

    public function coordinador(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'No autorizado.');
        }

        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin    = $request->fecha_fin;

        $id_usuario = $user->id_usuario ?? $user->id ?? null;
        $id_carrera = $user->id_carrera ?? null;
        $id_rol     = $user->id_rol ?? null;

        if (!$fecha_inicio || !$fecha_fin) {
            $bitacoras = $this->emptyPaginator($request);
            return view('bitacora_coordinador', compact('bitacoras'));
        }

        $resultados = $this->obtenerBitacoras(
            $fecha_inicio,
            $fecha_fin,
            $id_usuario,
            $id_rol,
            $id_carrera
        );

        $bitacoras = $this->paginateResults($resultados, $request, 10);

        return view('bitacora_coordinador', compact('bitacoras'));
    }

    public function secretariaAcademica(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'No autorizado.');
        }

        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin    = $request->fecha_fin;

        $id_usuario = $user->id_usuario ?? $user->id ?? null;
        $id_carrera = $user->id_carrera ?? null;
        $id_rol     = $user->id_rol ?? null;

        if (!$fecha_inicio || !$fecha_fin) {
            $bitacoras = $this->emptyPaginator($request);
            return view('bitacora_secretaria_academica', compact('bitacoras'));
        }

        $resultados = $this->obtenerBitacoras(
            $fecha_inicio,
            $fecha_fin,
            $id_usuario,
            $id_rol,
            $id_carrera
        );

        $bitacoras = $this->paginateResults($resultados, $request, 10);

        return view('bitacora_secretaria_academica', compact('bitacoras'));
    }

    public function secretariaGeneral(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'No autorizado.');
        }

        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin    = $request->fecha_fin;

        $id_usuario = $user->id_usuario ?? $user->id ?? null;
        $id_carrera = $user->id_carrera ?? null;
        $id_rol     = $user->id_rol ?? null;

        if (!$fecha_inicio || !$fecha_fin) {
            $bitacoras = $this->emptyPaginator($request);
            return view('bitacora_secretaria_general', compact('bitacoras'));
        }

        $resultados = $this->obtenerBitacoras(
            $fecha_inicio,
            $fecha_fin,
            $id_usuario,
            $id_rol,
            $id_carrera
        );

        $bitacoras = $this->paginateResults($resultados, $request, 10);

        return view('bitacora_secretaria_general', compact('bitacoras'));
    }

    private function obtenerBitacoras($fecha_inicio, $fecha_fin, $id_usuario, $id_rol, $id_carrera): array
    {
        return DB::select(
            'CALL SEL_BITACORA_TRAMITE(?,?,?,?,?)',
            [
                $fecha_inicio,
                $fecha_fin,
                $id_usuario,
                $id_rol,
                $id_carrera,
            ]
        );
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

    private function emptyPaginator(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            [],
            0,
            $perPage,
            (int) $request->get('page', 1),
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
