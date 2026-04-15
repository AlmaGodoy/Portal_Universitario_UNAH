@extends('layouts.app-coordinador')

@section('content')
@vite(['resources/css/bitacora.css', 'resources/js/bitacora.js'])


<div class="container-fluid py-4">

    <!-- 📌 TÍTULO -->
    <div class="text-center mb-4">
        <h2 class="fw-bold">
            Bitácora de Trámites - Coordinador
        </h2>
        <p class="text-muted">
            Consulta y seguimiento de trámites por rango de fechas
        </p>
    </div>

    <!-- 📅 FILTRO POR FECHAS -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('bitacora.index') }}">

                <div class="row align-items-end">

                    <!-- Fecha Inicio -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            Fecha Inicio
                        </label>

                        <input
                            type="date"
                            name="fecha_inicio"
                            class="form-control"
                            value="{{ request('fecha_inicio') }}"
                            required>
                    </div>

                    <!-- Fecha Fin -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            Fecha Fin
                        </label>

                        <input
                            type="date"
                            name="fecha_fin"
                            class="form-control"
                            value="{{ request('fecha_fin') }}"
                            required>
                    </div>

                    <!-- Botón Buscar -->
                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="btn btn-primary w-100">

                            🔍 Buscar Registros

                        </button>

                    </div>
                     <div class="col-md-2">
                            <!-- Limpiar -->
                            <a href="{{ route('bitacora.index') }}"
                                class="btn btn-dark w-100" >
                                🧹 Limpiar
                            </a>
                     </div>

                </div>

            </form>

        </div>
    </div>

    <!-- 📋 TABLA DE RESULTADOS -->
    <div class="card shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark text-center">

                        <tr>

                            <th>#</th>

                            <th>Fecha</th>

                            <th>Usuario</th>

                            <th>Carrera</th>

                            <th>Trámite</th>

                            <th>Estado</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($bitacoras as $index => $bitacora)

                        <tr class="text-center">

                            <!-- Número consecutivo -->
                            <td>
                                {{ $loop->iteration + ($bitacoras->firstItem() - 1) }}
                            </td>

                            <!-- Fecha -->
                            <td>
                                {{ $bitacora->fecha ?? '—' }}
                            </td>

                            <!-- Usuario -->
                            <td>
                                {{ $bitacora->usuario ?? '—' }}
                            </td>

                            <!-- Carrera -->
                            <td>
                                {{ $bitacora->carrera ?? '—' }}
                            </td>

                            <!-- Trámite -->
                            <td>
                                {{ $bitacora->tramite ?? '—' }}
                            </td>

                            <!-- Estado -->
                            <td>

                                @if(($bitacora->estado ?? '') == 'FINALIZADO')

                                    <span class="badge bg-success">
                                        FINALIZADO
                                    </span>

                                @elseif(($bitacora->estado ?? '') == 'PENDIENTE')

                                    <span class="badge bg-warning text-dark">
                                        PENDIENTE
                                    </span>

                                @else

                                    <span class="badge bg-secondary">
                                        {{ $bitacora->estado ?? '—' }}
                                    </span>

                                @endif

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="6" class="text-center text-muted">

                                📭 No hay registros para mostrar

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <!-- 🔢 PAGINACIÓN -->
            @if($bitacoras instanceof \Illuminate\Pagination\LengthAwarePaginator)

                <div class="mt-3 d-flex justify-content-center">

                    {{ $bitacoras->links('pagination::bootstrap-5') }}

                </div>

            @endif

        </div>

    </div>

</div>

@endsection
