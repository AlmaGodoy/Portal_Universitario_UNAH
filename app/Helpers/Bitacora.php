<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class Bitacora
{
    /**
     * Inserta un registro en tbl_bitacora
     * @param int $idUsuario  (si no existe, usa 0)
     * @param string $accion  ej: login_exitoso
     * @param string|null $descripcion detalle
     * @param int|null $idObjeto opcional
     */
    public static function registrar(int $idUsuario, string $accion, ?string $descripcion = null, ?int $idObjeto = null): void
    {
        DB::table('tbl_bitacora')->insert([
            'id_usuario'   => $idUsuario,
            'id_objeto'    => $idObjeto,
            'accion'       => $accion,
            'fecha_accion' => now(),
            'descripcion'  => $descripcion,
        ]);
    }
}
