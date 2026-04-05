<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteTramitesExport implements FromCollection, WithHeadings
{
    protected $tramitesReporte;
    protected $incluirCarrera = false;

    public function __construct(array $tramitesReporte)
    {
        $this->tramitesReporte = $tramitesReporte;

        if (!empty($tramitesReporte)) {
            $primerRegistro = $tramitesReporte[array_key_first($tramitesReporte)];
            $this->incluirCarrera = array_key_exists('carrera', $primerRegistro);
        }
    }

    public function collection()
    {
        return new Collection(array_map(function ($tramite, $index) {
            $fila = [
                '#' => $index + 1,
                'Estudiante' => $tramite['estudiante'] ?? 'Sin nombre',
            ];

            if ($this->incluirCarrera) {
                $fila['Carrera'] = $tramite['carrera'] ?? 'Sin carrera';
            }

            $fila['Tipo de trámite'] = Str::of($tramite['tipo'] ?? 'No definido')
                ->replace('_', ' ')
                ->title()
                ->value();

            $fila['Fecha de solicitud'] = $tramite['fecha_solicitud'] ?? 'Sin fecha';
            $fila['Estado'] = strtoupper($tramite['estado'] ?? 'Pendiente');

            return $fila;
        }, $this->tramitesReporte, array_keys($this->tramitesReporte)));
    }

    public function headings(): array
    {
        $headings = [
            '#',
            'Estudiante',
        ];

        if ($this->incluirCarrera) {
            $headings[] = 'Carrera';
        }

        $headings[] = 'Tipo de trámite';
        $headings[] = 'Fecha de solicitud';
        $headings[] = 'Estado';

        return $headings;
    }
}
