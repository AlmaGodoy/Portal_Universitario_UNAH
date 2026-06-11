@extends('layouts.app-secretaria-academica')

@section('content')
@vite(['resources/css/auditoria.css', 'resources/js/auditoria.js'])

<div class="container-fluid py-4">

    <div class="text-center mb-4">
        <h2 class="fw-bold">
            Auditoría - Secretaría Administrativa
        </h2>

        <p class="text-muted">
            Visualización de sus registros personales de auditoría
        </p>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <form method="GET">

                <div class="row align-items-end">

                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            Fecha Inicio
                        </label>

                        <input
                            type="date"
                            name="fecha_inicio"
                            class="form-control"
                            value="{{ $fecha_inicio }}"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            Fecha Fin
                        </label>

                        <input
                            type="date"
                            name="fecha_fin"
                            class="form-control"
                            value="{{ $fecha_fin }}"
                            required
                        >
                    </div>

                    <div class="col-md-2">
                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            🔎 Filtrar
                        </button>
                    </div>

                    <div class="col-md-2">
                        <a
                            href="{{ route('auditoria.administrativa') }}"
                            class="btn btn-secondary w-100"
                        >
                            🧹 Limpiar
                        </a>
                    </div>

                </div>

            </form>

        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-striped table-hover align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>ID Usuario</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Detalle</th>
                        <th>Módulo</th>
                        <th>Fecha y Hora</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($auditorias as $a)
                        <tr>
                            <td>{{ $a->id_usuario ?? '—' }}</td>
                            <td>{{ $a->usuario ?? '—' }}</td>
                            <td>{{ $a->accion ?? '—' }}</td>
                            <td>{{ $a->descripcion ?? '—' }}</td>
                            <td>{{ $a->modulo ?? '—' }}</td>
                            <td>
                                {{ !empty($a->fecha) ? \Carbon\Carbon::parse($a->fecha)->format('d/m/Y H:i') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                ⚠️ No hay registros personales en el período seleccionado
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection
