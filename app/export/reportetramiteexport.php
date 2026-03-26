<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteTramitesExport implements FromCollection, WithHeadings
{
    protected $tramitesPendientes;

    public function __construct(array $tramitesPendientes)
    {
        $this->tramitesPendientes = $tramitesPendientes;
    }

    public function collection()
    {
        return new Collection(array_map(function ($tramite, $index) {
            return [
                '#' => $index + 1,
                'Estudiante' => $tramite['estudiante'] ?? 'Sin nombre',
                'Tipo de trámite' => $tramite['tipo'] ?? 'No definido',
                'Fecha de solicitud' => $tramite['fecha_solicitud'] ?? 'Sin fecha',
                'Estado' => $tramite['estado'] ?? 'Pendiente',
            ];
        }, $this->tramitesPendientes, array_keys($this->tramitesPendientes)));
    }

    public function headings(): array
    {
        return [
            '#',
            'Estudiante',
            'Tipo de trámite',
            'Fecha de solicitud',
            'Estado',
        ];
    }
}
