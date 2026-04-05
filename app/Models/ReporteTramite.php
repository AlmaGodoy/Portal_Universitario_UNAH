<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class ReporteTramite
{
    /**
     * Reporte para secretario/coordinador
     */
    public static function obtener($idPersonaEmpleado = null, $tipoTramite = null, $estadoResolucion = null)
    {
        return DB::select(
            'CALL SEL_REPORTE_TRAMITE_SECRET_COORD(?, ?, ?)',
            [
                $idPersonaEmpleado,
                $tipoTramite,
                $estadoResolucion
            ]
        );
    }

    /**
     * Reporte para Secretaría General
     */
    public static function obtenerSecretariaGeneral($idCarrera = null, $tipoTramite = null, $estadoResolucion = null, $mesReporte = null)
    {
        return DB::select(
            'CALL SEL_REPORTE_TRAMITE_SECRETARIA_GENERAL(?, ?, ?, ?)',
            [
                $idCarrera,
                $tipoTramite,
                $estadoResolucion,
                $mesReporte
            ]
        );
    }
}
