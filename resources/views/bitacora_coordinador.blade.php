@extends('layouts.app-coordinador')

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
                            <h1 class="brand-title">Bitácora - Coordinador</h1>
                            <span class="brand-subtitle">FCEAC - UNAH</span>
                        </div>
                    </div>
                </header>
            </div>

            <!-- ⚠️ MENSAJE DE ERROR -->
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 🔍 FORMULARIO DE FILTRO -->
            <!-- El coordinador solo puede filtrar por fechas (no por carrera) -->
            <form method="GET" action="{{ route('bitacora.coordinador') }}" class="row g-3 mb-4">

                <!-- 📅 Fecha de inicio -->
                <div class="col-md-4">
                    <label>Fecha Inicio</label>
                    <input type="date" name="fecha_inicio"
                        value="{{ request('fecha_inicio') }}"
                        class="form-control">
                </div>

                <!-- 📅 Fecha final -->
                <div class="col-md-4">
                    <label>Fecha Fin</label>
                    <input type="date" name="fecha_fin"
                        value="{{ request('fecha_fin') }}"
                        class="form-control">
                </div>

                <!-- 🔘 Botones -->
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <!-- Ejecuta la consulta -->
                    <button class="btn btn-primary w-50">Buscar</button>

                    <!-- Limpia filtros -->
                    <a href="{{ route('bitacora.coordinador') }}"
                       class="btn btn-secondary w-50">
                        Limpiar
                    </a>
                </div>

            </form>

            <!-- 📊 TABLA DE RESULTADOS -->
            <!-- Muestra únicamente registros de la carrera del coordinador -->
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

                                <!-- 👤 Usuario -->
                                <td>{{ $item->id_usuario ?? '' }}</td>

                            </tr>
                        @empty
                            <!-- ❌ Sin resultados -->
                            <tr>
                                <td colspan="6" class="text-muted text-center">
                                    No hay registros disponibles
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 🔢 PAGINACIÓN -->
            <!-- Navegación entre páginas -->
            @if(method_exists($bitacoras, 'links'))
            <div class="mt-3 d-flex justify-content-center">
                {{ $bitacoras->links('pagination::bootstrap-5') }}
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
