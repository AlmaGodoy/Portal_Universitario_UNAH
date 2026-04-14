<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Bitacora;// Importamos el modelo de la bitacora
class BitacoraController extends Controller
{


public function index(Request $request)
{
    $user = auth()->user();

    $fecha_inicio = $request->fecha_inicio;
    $fecha_fin = $request->fecha_fin;

    $id_usuario = null;
    $id_carrera = null;

    // 🎯 Lógica por rol
    switch ($user->id_rol) {

        case 2: // 🎓 Estudiante
            $id_usuario = $user->id;
            break;

        case 4: // 🧑‍💼 Coordinador
        case 5: // 🧾 Secretaría Académica
            break;

        case 1: // 🏛️ Secretaría General
            break;
    }

    // 🔴 Si no hay fechas → paginador vacío
    if (!$fecha_inicio || !$fecha_fin) {

        $bitacoras = new LengthAwarePaginator(
            [],
            0,
            10,
            request()->get('page', 1),
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );

        return view('bitacora_coordinador', compact('bitacoras'));
    }

    // ✅ Ejecutar procedimiento almacenado
    $resultados = DB::select(
        'CALL SEL_BITACORA_TRAMITE(?,?,?,?,?)',
        [
            $fecha_inicio,
            $fecha_fin,
            $id_usuario,
            $user->id_rol,
            $id_carrera
        ]
    );

    // 🔢 PAGINACIÓN MANUAL
    $perPage = 10;
    $page = request()->get('page', 1);

    $bitacoras = new LengthAwarePaginator(
        array_slice($resultados, ($page - 1) * $perPage, $perPage),
        count($resultados),
        $perPage,
        $page,
        [
            'path' => request()->url(),
            'query' => request()->query()
        ]
    );

    return view('bitacora_coordinador', compact('bitacoras'));
}



public function secretariaAcademica(Request $request)
{
     $user = auth()->user();

    $fecha_inicio = $request->fecha_inicio;
    $fecha_fin = $request->fecha_fin;

    // Secretaría General ve todo
    $id_usuario = $user->id_usuario;
    $id_carrera = $user->id_carrera;


    $bitacoras = [];

    if ($fecha_inicio && $fecha_fin) {

        $bitacoras = DB::select(
            'CALL SEL_BITACORA_TRAMITE(?,?,?,?,?)',
            [
                $fecha_inicio,
                $fecha_fin,
                $id_usuario,
                $user->id_rol,
                $id_carrera
            ]
        );

    }

    return view(
        'bitacora_secretaria_academica',
        compact('bitacoras')
    );
}

public function secretariaGeneral(Request $request)
{
    $user = auth()->user();

    $fecha_inicio = $request->fecha_inicio;
    $fecha_fin = $request->fecha_fin;

    // Secretaría General ve todo
    $id_usuario = $user->id_usuario;
    $id_carrera = $user->id_carrera;

    $bitacoras = [];

    if ($fecha_inicio && $fecha_fin) {

        $bitacoras = DB::select(
            'CALL SEL_BITACORA_TRAMITE(?,?,?,?,?)',
            [
                $fecha_inicio,
                $fecha_fin,
                $id_usuario,
                $user->id_rol,
                $id_carrera
            ]
        );

    }

    return view(
        'bitacora_secretaria_general',
        compact('bitacoras')
    );
}

}
