<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReporteTramitesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected Collection $tramites;

    public function __construct($tramites)
    {
        $this->tramites = collect($tramites);
    }

    public function collection()
    {
        return $this->tramites->map(function ($tramite) {
            $item = is_array($tramite) ? $tramite : (array) $tramite;

            return [
                'ID Trámite'         => $item['id_tramite'] ?? '',
                'Estudiante'         => $item['estudiante'] ?? '',
                'Carrera'            => $item['carrera'] ?? '',
                'Tipo de trámite'    => $item['tipo'] ?? '',
                'Estado actual'      => $item['estado_actual'] ?? ($item['estado'] ?? ''),
                'Fecha solicitud'    => $item['fecha_solicitud'] ?? '',
                'Observaciones'      => $item['observaciones_resolucion'] ?? ($item['observaciones'] ?? ''),
                'Fecha resolución'   => $item['fecha_resolucion'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID Trámite',
            'Estudiante',
            'Carrera',
            'Tipo de trámite',
            'Estado actual',
            'Fecha solicitud',
            'Observaciones',
            'Fecha resolución',
        ];
    }
}

