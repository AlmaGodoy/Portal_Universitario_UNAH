@extends('layouts.app-coordinador')

@section('content')
@vite(['resources/css/bitacora.css', 'resources/js/bitacora.js'])

<div class="container-fluid py-4">

    <div class="text-center mb-4">
        <h2 class="fw-bold">
            Bitácora de Trámites - Coordinador
        </h2>
        <p class="text-muted">
            Consulta y seguimiento de trámites por rango de fechas
        </p>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('bitacora.coordinador') }}">

                <div class="row align-items-end">

                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            Fecha Inicio
                        </label>
                        <input
                            type="date"
                            name="fecha_inicio"
                            class="form-control"
                            value="{{ request('fecha_inicio', now()->subMonth()->toDateString()) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            Fecha Fin
                        </label>
                        <input
                            type="date"
                            name="fecha_fin"
                            class="form-control"
                            value="{{ request('fecha_fin', now()->toDateString()) }}">
                    </div>

                    <div class="col-md-2">
                        <button
                            type="submit"
                            class="btn btn-primary w-100">
                            🔍 Buscar
                        </button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('bitacora.coordinador') }}"
                           class="btn btn-dark w-100">
                            🧹 Limpiar
                        </a>
                    </div>

                </div>

            </form>

        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>ID</th>
                            <th>Estudiante</th>
                            <th>Trámite</th>
                            <th>Acción</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($bitacoras as $item)
                            <tr>
                                <td class="text-center">{{ $item->id ?? '—' }}</td>
                                <td>{{ $item->estudiante ?? '—' }}</td>
                                <td>{{ $item->tramite ?? '—' }}</td>
                                <td>{{ $item->accion ?? '—' }}</td>
                                <td>{{ $item->descripcion ?? '—' }}</td>
                                <td class="text-center">
                                    {{ !empty($item->fecha) ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y H:i') : '—' }}
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

            @if($bitacoras instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-3 d-flex justify-content-center">
                    {{ $bitacoras->links('pagination::bootstrap-5') }}
                </div>
            @endif

        </div>
    </div>

</div>
@endsection
