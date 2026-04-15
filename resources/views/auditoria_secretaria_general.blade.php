@extends('layouts.app-secretaria')

@section('content')
@vite(['resources/css/auditoria.css', 'resources/js/auditoria.js'])

<div class="container-fluid py-4">

    <!-- 📌 TÍTULO -->
    <div class="text-center mb-4">

        <h2 class="fw-bold">
            Auditoría General del Sistema
        </h2>

        <p class="text-muted">
            Visualización global de todos los registros de auditoría
        </p>

    </div>

    <!-- 🔎 FILTROS -->
    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form method="GET">

                <div class="row align-items-end">

                    <!-- Fecha Inicio -->
                    <div class="col-md-3">

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

                    <!-- Fecha Fin -->
                    <div class="col-md-3">

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

                    <!-- Botón Filtrar -->
                    <div class="col-md-3">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            🔎 Filtrar
                        </button>

                    </div>

                    <!-- Botón Limpiar -->
                    <div class="col-md-3">

                        <a
                            href="{{ route('auditoria.general') }}"
                            class="btn btn-secondary w-100"
                        >
                            🧹 Limpiar
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- 📊 TABLA -->
    <div class="card shadow-sm">

        <div class="card-body table-responsive">

            <table class="table table-striped table-hover align-middle">

                <thead class="table-dark">

                    <tr>

                        <th>ID Usuario</th>
                        <th>Código Empleado</th>
                        <th>Acción</th>
                        <th>Detalle</th>
                        <th>Módulo</th>
                        <th>Fecha y Hora</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($auditorias as $a)

                        <tr>

                            <td>{{ $a->id_usuario }}</td>

                            <td>{{ $a->cod_empleado }}</td>

                            <td>{{ $a->Accion_Realizada }}</td>

                            <td>{{ $a->Detalle }}</td>

                            <td>{{ $a->Modulo_Afectado }}</td>

                            <td>{{ $a->fecha_y_hora }}</td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center text-muted">

                                ⚠️ No hay registros en el período seleccionado

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


@endsection
