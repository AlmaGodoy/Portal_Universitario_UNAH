<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class ReporteTramite
{
    /**
     * Ejecutar el reporte de trámites académicos
     */
    public static function obtener($tipoTramite = null, $estadoResolucion = null)
    {
        return DB::select(
            'CALL SEL_REPORTE_TRAMITE(?, ?)',
            [
                $tipoTramite,
                $estadoResolucion
            ]
        );
    }
}
