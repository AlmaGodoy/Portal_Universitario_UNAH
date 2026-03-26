<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Trámites</title>
    @vite(['resources/css/Reporte.css', 'resources/js/Reporte.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="fondo-reporte"></div>

<div class="contenedor-reporte">

    <div class="encabezado-reporte">
        <div class="encabezado-texto">
            <h1>Reporte de Trámites</h1>
            <p>Resumen mensual y seguimiento de estudiantes con trámites pendientes.</p>
        </div>
        <div class="encabezado-fecha">
            <span>Mes actual</span>
            <strong>{{ $mesActual }}</strong>
        </div>
    </div>

    @if(session('error'))
        <div class="mensaje-error">
            <strong>Error:</strong> {{ session('error') }}
        </div>
    @endif

    @if(isset($error))
        <div class="mensaje-error">
            <strong>Error:</strong> {{ $error }}
        </div>
    @endif

    <div class="panel">
        <div class="panel-header">
            <h2>Filtros del reporte</h2>
        </div>

        <div class="panel-body">
            <form method="GET" action="{{ url('/reporte-tramites-vista') }}" class="form-filtros" id="formFiltrosReporte">

                <div class="campo-filtro">
                    <label for="tipo_tramite">Tipo de trámite</label>
                    <div class="control-filtro">
                        <select name="tipo_tramite" id="tipo_tramite">
                            <option value="">Todos</option>
                            <option value="cambio_carrera" {{ ($tipoTramite ?? '') == 'cambio_carrera' ? 'selected' : '' }}>
                                Cambio de Carrera
                            </option>
                            <option value="cancelacion" {{ ($tipoTramite ?? '') == 'cancelacion' ? 'selected' : '' }}>
                                Cancelación de Clases
                            </option>
                        </select>
                    </div>
                </div>

                <div class="campo-filtro">
                    <label for="estado_resolucion">Estado</label>
                    <div class="control-filtro">
                        <select name="estado_resolucion" id="estado_resolucion">
                            <option value="">Todos</option>
                            <option value="aprobado" {{ ($estadoResolucion ?? '') == 'aprobado' ? 'selected' : '' }}>
                                Aprobado
                            </option>
                            <option value="rechazado" {{ ($estadoResolucion ?? '') == 'rechazado' ? 'selected' : '' }}>
                                Rechazado
                            </option>
                            <option value="pendiente" {{ ($estadoResolucion ?? '') == 'pendiente' ? 'selected' : '' }}>
                                Pendiente
                            </option>
                        </select>
                    </div>
                </div>

                <div class="campo-filtro">
                    <label for="mes_reporte">Mes</label>
                    <div class="control-filtro">
                        <select name="mes_reporte" id="mes_reporte">
                            <option value="">Totales</option>
                            <option value="1" {{ ($mesReporte ?? '') == '1' ? 'selected' : '' }}>Enero</option>
                            <option value="2" {{ ($mesReporte ?? '') == '2' ? 'selected' : '' }}>Febrero</option>
                            <option value="3" {{ ($mesReporte ?? '') == '3' ? 'selected' : '' }}>Marzo</option>
                            <option value="4" {{ ($mesReporte ?? '') == '4' ? 'selected' : '' }}>Abril</option>
                            <option value="5" {{ ($mesReporte ?? '') == '5' ? 'selected' : '' }}>Mayo</option>
                            <option value="6" {{ ($mesReporte ?? '') == '6' ? 'selected' : '' }}>Junio</option>
                            <option value="7" {{ ($mesReporte ?? '') == '7' ? 'selected' : '' }}>Julio</option>
                            <option value="8" {{ ($mesReporte ?? '') == '8' ? 'selected' : '' }}>Agosto</option>
                            <option value="9" {{ ($mesReporte ?? '') == '9' ? 'selected' : '' }}>Septiembre</option>
                            <option value="10" {{ ($mesReporte ?? '') == '10' ? 'selected' : '' }}>Octubre</option>
                            <option value="11" {{ ($mesReporte ?? '') == '11' ? 'selected' : '' }}>Noviembre</option>
                            <option value="12" {{ ($mesReporte ?? '') == '12' ? 'selected' : '' }}>Diciembre</option>
                        </select>
                    </div>
                </div>

                <div class="acciones-filtro">
                    <button type="submit" class="btn-filtrar">Filtrar</button>
                    <a href="{{ url('/reporte-tramites-vista') }}" class="btn-limpiar">Limpiar</a>
                </div>
            </form>

            <div class="acciones-exportacion">
                <a href="{{ route('reporte.tramites.pdf', [
                    'tipo_tramite' => $tipoTramite,
                    'estado_resolucion' => $estadoResolucion,
                    'mes_reporte' => $mesReporte
                ]) }}" class="btn-exportar-pdf">
                    Exportar PDF
                </a>

                <a href="{{ route('reporte.tramites.excel', [
                    'tipo_tramite' => $tipoTramite,
                    'estado_resolucion' => $estadoResolucion,
                    'mes_reporte' => $mesReporte
                ]) }}" class="btn-exportar-excel">
                    Exportar Excel
                </a>
            </div>
        </div>
    </div>

    <div class="tarjetas-resumen">
        <div class="tarjeta tarjeta-aprobados">
            <div class="icono">✔</div>
            <div class="contenido">
                <h3>Trámites Aprobados</h3>
                <p>{{ $aprobadosMes }}</p>
            </div>
        </div>

        <div class="tarjeta tarjeta-rechazados">
            <div class="icono">✖</div>
            <div class="contenido">
                <h3>Trámites Rechazados</h3>
                <p>{{ $rechazadosMes }}</p>
            </div>
        </div>

        <div class="tarjeta tarjeta-pendientes">
            <div class="icono">⏳</div>
            <div class="contenido">
                <h3>Trámites Pendientes</h3>
                <p>{{ $pendientesTotal }}</p>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2>Cantidad de trámites aprobados vs. rechazados en el mes</h2>
        </div>
        <div class="panel-body">
            <div class="grafica-wrapper">
                <canvas
                    id="graficaTramites"
                    data-aprobados="{{ $aprobadosMes }}"
                    data-rechazados="{{ $rechazadosMes }}">
                </canvas>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2>Listado de estudiantes con trámites pendientes</h2>
        </div>
        <div class="panel-body">
            <div class="tabla-responsive">
                <table class="tabla-tramites">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Estudiante</th>
                            <th>Tipo de trámite</th>
                            <th>Fecha de solicitud</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tramitesPendientes as $index => $tramite)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $tramite['estudiante'] ?? 'Sin nombre' }}</td>
                                <td>{{ $tramite['tipo'] ?? 'No definido' }}</td>
                                <td>{{ $tramite['fecha_solicitud'] ?? 'Sin fecha' }}</td>
                                <td>
                                    <span class="estado estado-{{ strtolower($tramite['estado'] ?? 'pendiente') }}">
                                        {{ ucfirst($tramite['estado'] ?? 'pendiente') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="sin-registros">
                                    No hay trámites pendientes con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

</body>
</html>
