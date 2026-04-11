<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Bitacora; // Importamos el modelo de la bitacora

class BitacoraController extends Controller
{

// 🔵 Coordinador
public function coordinador()
{
    $bitacoras = $this->obtenerDatos();
    return view('bitacora.bitacora_coordinador', compact('bitacoras'));
}

// 🟡 Secretaria Académica
public function secretariaAcademica()
{
    $bitacoras = $this->obtenerDatos();
    return view('bitacora.bitacora_secretaria_academica', compact('bitacoras'));
}

// 🔴 Secretaria General
public function secretariaGeneral(Request $request)
{
    $carreras = DB::table('tbl_carrera')->get();

    $bitacoras = $this->obtenerDatos($request);

    return view('bitacora.bitacora_secretaria_general', compact('bitacoras','carreras'));
}

private function obtenerDatos($request = null)
{
    $user = auth()->user();

    $fecha_inicio = $request->fecha_inicio ?? now()->subMonth()->toDateString();
    $fecha_fin = $request->fecha_fin ?? now()->toDateString();
    $id_carrera = $request->id_carrera ?? null;

    return collect(DB::select(
        'CALL SEL_BITACORA_SISTEMA(?, ?, ?, ?, ?)',
        [
            $fecha_inicio,
            $fecha_fin,
            $user->id_usuario,
            $user->id_rol,
            $id_carrera
        ]
    ));
}

}
