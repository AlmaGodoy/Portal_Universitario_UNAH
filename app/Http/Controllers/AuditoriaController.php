<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditoriaController extends Controller
{
    public function redirectAuditoria()
    {
        $user = auth()->user();

        if ($user->id_rol == 1) {
            return redirect()->route('auditoria.administrativa');
        } elseif ($user->id_rol == 4) {
            return redirect()->route('auditoria.coordinador');
        } elseif ($user->id_rol == 5) {
            return redirect()->route('auditoria.general');
        }

        abort(403);
    }

    public function administrativa(Request $request)
    {
        $user = auth()->user();

        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;

        $id_usuario = $user->id_usuario;
        $id_rol = $user->id_rol;
        $id_carrera = null;

        $auditorias = [];

        if ($fecha_inicio && $fecha_fin) {
            $auditorias = DB::select(
                'CALL SEL_AUDITORIA(?,?,?,?,?)',
                [
                    $fecha_inicio,
                    $fecha_fin,
                    $id_usuario,
                    $id_rol,
                    $id_carrera
                ]
            );
        }

        return view(
            'auditoria_secretaria_academica',
            compact(
                'auditorias',
                'fecha_inicio',
                'fecha_fin'
            )
        );
    }

    public function coordinador(Request $request)
    {
        $user = auth()->user();

        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;

        $id_usuario = $user->id_usuario;
        $id_rol = $user->id_rol;
        $id_carrera = $user->id_carrera;

        $resultados = [];

        if ($fecha_inicio && $fecha_fin) {
            $resultados = DB::select(
                'CALL SEL_AUDITORIA(?,?,?,?,?)',
                [
                    $fecha_inicio,
                    $fecha_fin,
                    $id_usuario,
                    $id_rol,
                    $id_carrera
                ]
            );
        }

        $perPage = 10;
        $page = request()->get('page', 1);

        $registros = new LengthAwarePaginator(
            array_slice($resultados, ($page - 1) * $perPage, $perPage),
            count($resultados),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );

        return view(
            'auditoria_coordinador',
            compact(
                'registros',
                'fecha_inicio',
                'fecha_fin'
            )
        );
    }

    public function general(Request $request)
    {
        $user = auth()->user();

        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;

        $id_usuario = null;
        $id_rol = $user->id_rol;
        $id_carrera = null;

        $auditorias = [];

        if ($fecha_inicio && $fecha_fin) {
            $auditorias = DB::select(
                'CALL SEL_AUDITORIA(?,?,?,?,?)',
                [
                    $fecha_inicio,
                    $fecha_fin,
                    $id_usuario,
                    $id_rol,
                    $id_carrera
                ]
            );
        }

        return view(
            'auditoria_secretaria_general',
            compact(
                'auditorias',
                'fecha_inicio',
                'fecha_fin'
            )
        );
    }
}
