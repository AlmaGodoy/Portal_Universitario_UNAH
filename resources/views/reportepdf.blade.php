<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Trámites Académicos</title>

    @php
        $pdfCss = file_exists(resource_path('css/reporte.css'))
            ? file_get_contents(resource_path('css/reporte.css'))
            : '';

        $mostrarCarrera = !empty($tramitesReporte) && array_key_exists('carrera', $tramitesReporte[0]);
    @endphp

    <style>
        {!! $pdfCss !!}
    </style>
</head>
<body class="pdf-body">

    <table class="inst-header-table">
        <tr>
            <td class="inst-header-left">
                <div class="inst-universidad">Universidad Nacional Autónoma de Honduras</div>
                <div class="inst-sistema">Sistema de Gestión de Trámites Académicos</div>
                <div class="inst-documento">Informe Institucional</div>
            </td>
            <td class="inst-header-right">
                <div class="inst-meta-label">Fecha de emisión</div>
                <div class="inst-meta-value">{{ now()->format('d/m/Y') }}</div>
                <div class="inst-meta-label espacio-superior">Mes de referencia</div>
                <div class="inst-meta-value">{{ $mesActual }}</div>
            </td>
        </tr>
    </table>

    <div class="inst-titulo-principal">
        REPORTE DE TRÁMITES ACADÉMICOS
    </div>

    <div class="inst-subtitulo">
        Resumen mensual y listado general de trámites académicos
    </div>

    <table class="inst-resumen-table">
        <tr>
            <td class="inst-resumen-box">
                <div class="inst-box-titulo">Trámites Aprobados</div>
                <div class="inst-box-valor color-aprobado">{{ $aprobadosMes }}</div>
            </td>
            <td class="inst-resumen-box">
                <div class="inst-box-titulo">Trámites Rechazados</div>
                <div class="inst-box-valor color-rechazado">{{ $rechazadosMes }}</div>
            </td>
            <td class="inst-resumen-box">
                <div class="inst-box-titulo">Trámites Pendientes</div>
                <div class="inst-box-valor color-pendiente">{{ $pendientesTotal }}</div>
            </td>
            <td class="inst-resumen-box">
                <div class="inst-box-titulo">En Revisión</div>
                <div class="inst-box-valor color-revision">{{ $revisionTotal ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <div class="inst-seccion-titulo">
        Detalle general de trámites académicos
    </div>

    <table class="inst-tabla-datos">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-estudiante">Estudiante</th>

                @if($mostrarCarrera)
                    <th class="col-carrera">Carrera</th>
                @endif

                <th class="col-tipo">Tipo de trámite</th>
                <th class="col-fecha">Fecha de<br>solicitud</th>
                <th class="col-estado">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tramitesReporte as $index => $tramite)
                @php
                    $estadoClase = strtolower(trim($tramite['estado'] ?? 'pendiente'));
                    $tipoBonito = \Illuminate\Support\Str::of($tramite['tipo'] ?? 'No definido')
                        ->replace('_', ' ')
                        ->title();

                    $fechaOriginal = $tramite['fecha_solicitud'] ?? 'Sin fecha';
                    $fechaFormateada = 'Sin fecha';

                    if (!empty($tramite['fecha_solicitud']) && $tramite['fecha_solicitud'] !== 'Sin fecha') {
                        try {
                            $fechaCarbon = \Carbon\Carbon::parse($tramite['fecha_solicitud']);
                            $fechaFormateada = $fechaCarbon->format('Y-m-d') . '<br>' . $fechaCarbon->format('H:i:s');
                        } catch (\Exception $e) {
                            $fechaFormateada = e($fechaOriginal);
                        }
                    }

                    $badgeClass = match ($estadoClase) {
                        'aprobada', 'aprobado' => 'inst-badge inst-badge-aprobada',
                        'rechazada', 'rechazado' => 'inst-badge inst-badge-rechazada',
                        'revision', 'revisión' => 'inst-badge inst-badge-revision',
                        default => 'inst-badge inst-badge-pendiente',
                    };
                @endphp
                <tr>
                    <td class="texto-centro col-num">{{ $index + 1 }}</td>
                    <td class="col-estudiante">{{ $tramite['estudiante'] ?? 'Sin nombre' }}</td>

                    @if($mostrarCarrera)
                        <td class="col-carrera">{{ $tramite['carrera'] ?? 'Sin carrera' }}</td>
                    @endif

                    <td class="col-tipo">{{ $tipoBonito }}</td>
                    <td class="texto-centro col-fecha fecha-columna">{!! $fechaFormateada !!}</td>
                    <td class="texto-centro col-estado">
                        <span class="{{ $badgeClass }}">
                            {{ strtoupper($tramite['estado'] ?? 'pendiente') }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $mostrarCarrera ? 6 : 5 }}" class="inst-sin-datos">
                        No hay trámites registrados con los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="inst-footer-table">
        <tr>
            <td class="inst-footer-left">
                Documento generado automáticamente por el sistema institucional de la UNAH
            </td>
            <td class="inst-footer-right">
                Página 1
            </td>
        </tr>
    </table>

</body>
</html>
