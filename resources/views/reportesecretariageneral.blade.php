@extends('layouts.app')

@section('titulo', 'Reporte de Trámites - Secretaría General')

@section('content')
    @vite(['resources/css/reporte.css', 'resources/js/reporte_secretaria_general.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="pagina-reporte">

    <div class="fondo-reporte"></div>

    <div class="contenedor-reporte">

        <div class="encabezado-reporte">
            <div class="encabezado-texto">
                <h1>Reporte de Trámites - Secretaría General</h1>
                <p>Consulta general por carrera o por el total acumulado de todas las carreras.</p>
            </div>
            <div class="encabezado-fecha">
                <span>Mes de referencia</span>
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
                <form method="GET"
                      action="{{ route('reporte.tramites.secretaria_general.vista') }}"
                      class="form-filtros form-filtros-con-exportar"
                      id="formFiltrosReporteSecretariaGeneral">

                    <div class="campo-filtro">
                        <label for="id_carrera">Carrera</label>
                        <div class="control-filtro">
                            <select name="id_carrera" id="id_carrera">
                                <option value="0">Todas las carreras</option>
                                @foreach($carreras as $carrera)
                                    <option value="{{ $carrera->id_carrera }}"
                                        {{ (string)($idCarrera ?? '') === (string)$carrera->id_carrera ? 'selected' : '' }}>
                                        {{ $carrera->nombre_carrera }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

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
                                <option value="aprobada" {{ ($estadoResolucion ?? '') == 'aprobada' ? 'selected' : '' }}>
                                    Aprobada
                                </option>
                                <option value="rechazada" {{ ($estadoResolucion ?? '') == 'rechazada' ? 'selected' : '' }}>
                                    Rechazada
                                </option>
                                <option value="pendiente" {{ ($estadoResolucion ?? '') == 'pendiente' ? 'selected' : '' }}>
                                    Pendiente
                                </option>
                                <option value="revision" {{ ($estadoResolucion ?? '') == 'revision' ? 'selected' : '' }}>
                                    Revisión
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="campo-filtro">
                        <label for="mes_reporte">Mes</label>
                        <div class="control-filtro">
                            <select name="mes_reporte" id="mes_reporte">
                                <option value="">Totales</option>
                                <option value="1"  {{ ($mesReporte ?? '') == '1' ? 'selected' : '' }}>Enero</option>
                                <option value="2"  {{ ($mesReporte ?? '') == '2' ? 'selected' : '' }}>Febrero</option>
                                <option value="3"  {{ ($mesReporte ?? '') == '3' ? 'selected' : '' }}>Marzo</option>
                                <option value="4"  {{ ($mesReporte ?? '') == '4' ? 'selected' : '' }}>Abril</option>
                                <option value="5"  {{ ($mesReporte ?? '') == '5' ? 'selected' : '' }}>Mayo</option>
                                <option value="6"  {{ ($mesReporte ?? '') == '6' ? 'selected' : '' }}>Junio</option>
                                <option value="7"  {{ ($mesReporte ?? '') == '7' ? 'selected' : '' }}>Julio</option>
                                <option value="8"  {{ ($mesReporte ?? '') == '8' ? 'selected' : '' }}>Agosto</option>
                                <option value="9"  {{ ($mesReporte ?? '') == '9' ? 'selected' : '' }}>Septiembre</option>
                                <option value="10" {{ ($mesReporte ?? '') == '10' ? 'selected' : '' }}>Octubre</option>
                                <option value="11" {{ ($mesReporte ?? '') == '11' ? 'selected' : '' }}>Noviembre</option>
                                <option value="12" {{ ($mesReporte ?? '') == '12' ? 'selected' : '' }}>Diciembre</option>
                            </select>
                        </div>
                    </div>

                    <div class="acciones-filtro acciones-filtro-con-exportar">
                        <div class="acciones-filtro-izquierda">
                            <button type="submit" class="btn-filtrar">Filtrar</button>
                            <a href="{{ route('reporte.tramites.secretaria_general.vista') }}" class="btn-limpiar">Limpiar</a>
                        </div>

                        <div class="acciones-exportar acciones-exportar-derecha">
                            <a
                                href="{{ route('reporte.tramites.pdf', [
                                    'id_carrera' => request('id_carrera'),
                                    'tipo_tramite' => request('tipo_tramite'),
                                    'estado_resolucion' => request('estado_resolucion'),
                                    'mes_reporte' => request('mes_reporte'),
                                ]) }}"
                                class="btn-exportar btn-exportar-pdf">
                                Exportar PDF
                            </a>

                            <a
                                href="{{ route('reporte.tramites.excel', [
                                    'id_carrera' => request('id_carrera'),
                                    'tipo_tramite' => request('tipo_tramite'),
                                    'estado_resolucion' => request('estado_resolucion'),
                                    'mes_reporte' => request('mes_reporte'),
                                ]) }}"
                                class="btn-exportar btn-exportar-excel">
                                Exportar Excel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="tarjetas-resumen tarjetas-resumen-cuatro">
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

            <div class="tarjeta tarjeta-revision">
                <div class="icono">📝</div>
                <div class="contenido">
                    <h3>Trámites en Revisión</h3>
                    <p>{{ $revisionTotal ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>Resumen gráfico de trámites por estado</h2>
            </div>
            <div class="panel-body">
                <div class="grafica-wrapper">
                    <canvas
                        id="graficaTramites"
                        data-aprobados="{{ $aprobadosMes }}"
                        data-rechazados="{{ $rechazadosMes }}"
                        data-pendientes="{{ $pendientesTotal }}"
                        data-revision="{{ $revisionTotal ?? 0 }}">
                    </canvas>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>Listado general de trámites académicos</h2>
            </div>
            <div class="panel-body">
                <div class="tabla-responsive">
                    <table class="tabla-tramites">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Estudiante</th>
                                <th>Carrera</th>
                                <th>Tipo de trámite</th>
                                <th>Fecha de solicitud</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tramitesReporte as $index => $tramite)
                                @php
                                    $estadoClase = strtolower(trim($tramite['estado'] ?? 'pendiente'));
                                    $tipoBonito = \Illuminate\Support\Str::of($tramite['tipo'] ?? 'No definido')
                                        ->replace('_', ' ')
                                        ->title();
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $tramite['estudiante'] ?? 'Sin nombre' }}</td>
                                    <td>{{ $tramite['carrera'] ?? 'Sin carrera' }}</td>
                                    <td>{{ $tipoBonito }}</td>
                                    <td>{{ $tramite['fecha_solicitud'] ?? 'Sin fecha' }}</td>
                                    <td>
                                        <span class="estado estado-{{ $estadoClase }}">
                                            {{ strtoupper($tramite['estado'] ?? 'pendiente') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="sin-registros">
                                        No hay trámites con los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
