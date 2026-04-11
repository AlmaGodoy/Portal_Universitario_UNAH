@extends('layouts.app-secretaria')
@section('content')
@vite(['resources/css/bitacora.css', 'resources/js/bitacora.js'])

<div class="container py-4 security-page">
    <div class="row g-4">
        <div class="col-12">

            <!-- 🔷 ENCABEZADO DEL MÓDULO -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <header class="topbar">
                    <div class="brand">
                        <img alt="Logo PumaGestión" class="brand-logo">

                        <div class="brand-text">
                            <h1 class="brand-title">Bitácora - Secretaría General</h1>
                            <span class="brand-subtitle">FCEAC - UNAH</span>
                        </div>
                    </div>
                </header>
            </div>

            <!-- ⚠️ MENSAJE DE ERROR DEL SISTEMA -->
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 🔍 FORMULARIO DE FILTRO -->
            <!-- Permite filtrar la bitácora por rango de fechas y carrera -->
            <form method="GET" action="{{ route('bitacora.general') }}" class="row g-3 mb-4">

                <!-- 📅 Fecha de inicio -->
                <div class="col-md-3">
                    <label>Fecha Inicio</label>
                    <input type="date" name="fecha_inicio"
                        value="{{ request('fecha_inicio') }}"
                        class="form-control" required>
                </div>

                <!-- 📅 Fecha final -->
                <div class="col-md-3">
                    <label>Fecha Fin</label>
                    <input type="date" name="fecha_fin"
                        value="{{ request('fecha_fin') }}"
                        class="form-control" required>
                </div>

                <!-- 🎓 Filtro por carrera (solo Secretaría General) -->
                <div class="col-md-3">
                    <label>Carrera</label>
                    <select name="id_carrera" class="form-select">
                        <option value="">Todas las carreras</option>
                        @foreach($carreras as $c)
                            <option value="{{ $c->id_carrera }}"
                                {{ request('id_carrera') == $c->id_carrera ? 'selected' : '' }}>
                                {{ $c->nombre_carrera }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 🔘 Botones de acción -->
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <!-- Ejecuta la consulta -->
                    <button class="btn btn-primary w-50">Buscar</button>

                    <!-- Limpia filtros -->
                    <a href="{{ route('bitacora.general') }}"
                       class="btn btn-secondary w-50">
                        Limpiar
                    </a>
                </div>

            </form>

            <!-- 📊 TABLA DE RESULTADOS -->
            <!-- Muestra los registros de la bitácora según los filtros -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">

                    <!-- Encabezados -->
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Estudiante</th>
                            <th>Trámite</th>
                            <th>Estado</th>
                            <th>Observación</th>
                            <th>Usuario</th>
                            <th>Carrera</th>
                        </tr>
                    </thead>

                    <!-- Cuerpo -->
                    <tbody>
                        @forelse($bitacoras as $index => $item)
                            <tr>

                                <!-- 🔢 Numeración dinámica -->
                                <td>
                                    {{ method_exists($bitacoras, 'firstItem')
                                        ? $bitacoras->firstItem() + $index
                                        : $index + 1 }}
                                </td>

                                <!-- 📌 Datos -->
                                <td>{{ $item->Estudiante ?? '' }}</td>
                                <td>{{ $item->Tramite ?? '' }}</td>

                                <!-- 🎨 Estado con colores -->
                                <td>
                                    <span class="badge
                                        @if($item->Estado == 'Aprobado') bg-success
                                        @elseif($item->Estado == 'Rechazado') bg-danger
                                        @else bg-secondary
                                        @endif">
                                        {{ $item->Estado ?? '' }}
                                    </span>
                                </td>

                                <!-- 📝 Observación -->
                                <td class="text-start">
                                    {{ $item->observacion_dictamen ?? '' }}
                                </td>

                                <!-- 👤 Usuario responsable -->
                                <td>{{ $item->id_usuario ?? '' }}</td>

                                <!-- 🎓 Carrera -->
                                <td>{{ $item->carrera ?? 'N/A' }}</td>

                            </tr>
                        @empty
                            <!-- ❌ Mensaje cuando no hay datos -->
                            <tr>
                                <td colspan="7" class="text-muted text-center">
                                    No hay registros para los filtros seleccionados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!--  PAGINACIÓN -->
            <!-- Permite navegar entre páginas de resultados -->
            @if(method_exists($bitacoras, 'links'))
            <div class="mt-3 d-flex justify-content-center">
                {{ $bitacoras->links('pagination::bootstrap-5') }}
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

