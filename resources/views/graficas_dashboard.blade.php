@php
    $rootId = $rootId ?? 'graficasDashboard';
    $apiUrl = $apiUrl ?? '';
    $scopeLabel = $scopeLabel ?? 'módulo';
    $scopeNote = $scopeNote ?? 'Mostrando datos generales.';
    $breakdownLabel = $breakdownLabel ?? 'grupo';

    $modoFiltro = $modoFiltro ?? 'carrera'; // carrera | departamento | ninguno

    $aniosDisponibles = $aniosDisponibles ?? [];
    $carreras = $carreras ?? collect();
    $departamentos = $departamentos ?? collect();

    $anioSeleccionado = $anio ?? request('anio') ?? (count($aniosDisponibles) ? $aniosDisponibles[0] : date('Y'));
    $mesSeleccionado = $mes ?? request('mes') ?? '';
    $idCarreraSeleccionada = $idCarreraSeleccionada ?? request('id_carrera') ?? '';
    $idDepartamentoSeleccionado = $idDepartamentoSeleccionado ?? request('id_departamento') ?? '';
@endphp

<section class="content graf-wrap">
    <div id="{{ $rootId }}"
         data-api-url="{{ $apiUrl }}"
         data-scope-label="{{ $scopeLabel }}"
         data-scope-note="{{ $scopeNote }}"
         data-breakdown-label="{{ $breakdownLabel }}">

        <div class="graf-toolbar">
            <div class="graf-toolbar-left">
                <p class="graf-label mb-0">
                    <i class="fas fa-filter"></i> Filtrar gráficas
                </p>

                <select id="anioSelectGraficas" class="graf-select">
                    @forelse($aniosDisponibles as $anioItem)
                        <option value="{{ $anioItem }}" {{ (string)$anioSeleccionado === (string)$anioItem ? 'selected' : '' }}>
                            {{ $anioItem }}
                        </option>
                    @empty
                        <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                    @endforelse
                </select>

                <select id="mesSelectGraficas" class="graf-select">
                    <option value="">Todos los meses</option>
                    <option value="1"  {{ (string)$mesSeleccionado === '1' ? 'selected' : '' }}>Enero</option>
                    <option value="2"  {{ (string)$mesSeleccionado === '2' ? 'selected' : '' }}>Febrero</option>
                    <option value="3"  {{ (string)$mesSeleccionado === '3' ? 'selected' : '' }}>Marzo</option>
                    <option value="4"  {{ (string)$mesSeleccionado === '4' ? 'selected' : '' }}>Abril</option>
                    <option value="5"  {{ (string)$mesSeleccionado === '5' ? 'selected' : '' }}>Mayo</option>
                    <option value="6"  {{ (string)$mesSeleccionado === '6' ? 'selected' : '' }}>Junio</option>
                    <option value="7"  {{ (string)$mesSeleccionado === '7' ? 'selected' : '' }}>Julio</option>
                    <option value="8"  {{ (string)$mesSeleccionado === '8' ? 'selected' : '' }}>Agosto</option>
                    <option value="9"  {{ (string)$mesSeleccionado === '9' ? 'selected' : '' }}>Septiembre</option>
                    <option value="10" {{ (string)$mesSeleccionado === '10' ? 'selected' : '' }}>Octubre</option>
                    <option value="11" {{ (string)$mesSeleccionado === '11' ? 'selected' : '' }}>Noviembre</option>
                    <option value="12" {{ (string)$mesSeleccionado === '12' ? 'selected' : '' }}>Diciembre</option>
                </select>

                @if($modoFiltro === 'carrera')
                    <select id="filtroCarrera" class="graf-select">
                        <option value="">Todas las carreras</option>
                        @foreach($carreras as $carrera)
                            <option value="{{ $carrera->id_carrera }}"
                                {{ (string)$idCarreraSeleccionada === (string)$carrera->id_carrera ? 'selected' : '' }}>
                                {{ $carrera->nombre_carrera }}
                            </option>
                        @endforeach
                    </select>
                @elseif($modoFiltro === 'departamento')
                    <select id="filtroDepartamento" class="graf-select">
                        <option value="">Todos los departamentos</option>
                        @foreach($departamentos as $departamento)
                            <option value="{{ $departamento->id_departamento }}"
                                {{ (string)$idDepartamentoSeleccionado === (string)$departamento->id_departamento ? 'selected' : '' }}>
                                {{ $departamento->nombre_departamento }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="graf-status" id="estadoCargaGraficas">
                Listo para consultar estadísticas
            </div>
        </div>

        <div class="scope-note-box" id="scopeNoteGraficas">
            {{ $scopeNote }}
        </div>

        <div class="stats-grid">
            <div class="stat-card bg-a">
                <i class="fas fa-ban bg"></i>
                <div class="title">Cancelaciones Excepcionales</div>
                <div class="value" id="totalCancelaciones">0</div>
                <div class="foot">Total del filtro actual</div>
            </div>

            <div class="stat-card bg-b">
                <i class="fas fa-right-left bg"></i>
                <div class="title">Cambios de Carrera</div>
                <div class="value" id="totalCambios">0</div>
                <div class="foot">Total del filtro actual</div>
            </div>
        </div>

        <div class="donut-grid">
            <div class="chart-card donut-card">
                <div class="chart-head">
                    <h4>Distribución de Cancelaciones</h4>
                    <p>Visualización por {{ $breakdownLabel }}</p>
                </div>

                <div class="chart-body donut-body">
                    <div class="donut-canvas-wrap">
                        <div id="donutCancelaciones" class="donut-render-host"></div>
                    </div>

                    <div class="donut-empty" id="donutEmptyCancelaciones">
                        No hay datos disponibles.
                    </div>
                </div>
            </div>

            <div class="chart-card donut-card">
                <div class="chart-head">
                    <h4>Distribución de Cambios de Carrera</h4>
                    <p>Visualización por {{ $breakdownLabel }}</p>
                </div>

                <div class="chart-body donut-body">
                    <div class="donut-canvas-wrap">
                        <div id="donutCambios" class="donut-render-host"></div>
                    </div>

                    <div class="donut-empty" id="donutEmptyCambios">
                        No hay datos disponibles.
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-grid chart-grid-single">
            <div class="chart-card">
                <div class="chart-head">
                    <h4>Comparativo por Período</h4>
                    <p>Cancelaciones vs Cambios de Carrera</p>
                </div>

                <div class="chart-body">
                    <div id="chartComparativo" class="bar-chart-placeholder">
                        Cargando información...
                    </div>
                    <div class="chart-note" id="noteComparativo">Esperando datos.</div>
                </div>
            </div>
        </div>

    </div>
</section>
