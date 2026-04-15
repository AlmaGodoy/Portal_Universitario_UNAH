@extends('layouts.app-secretaria-academica')

@section('content')
@vite(['resources/css/bitacora.css', 'resources/js/bitacora.js'])

<div class="container-fluid py-4">

    <!-- 📌 TÍTULO -->
    <div class="text-center mb-4">
        <h2 class="fw-bold">
            Bitácora de Trámites - Secretaría Académica
        </h2>
    </div>

    <!-- 🔎 FILTROS -->
    <div class="row justify-content-center mb-4">

        <div class="col-md-8 col-lg-7">

            <div class="card shadow-sm">

                <div class="card-body">

                    <!-- FORMULARIO -->
                    <form method="GET"
                          action="{{ route('bitacora.secretaria_academica') }}"
                          class="filtro-form">

                        <div class="row align-items-end">

                            <!-- Fecha Inicio -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Fecha Inicio
                                </label>

                                <input
                                    type="date"
                                    name="fecha_inicio"
                                    class="form-control"
                                    value="{{ request('fecha_inicio') }}">
                            </div>

                            <!-- Fecha Fin -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Fecha Fin
                                </label>

                                <input
                                    type="date"
                                    name="fecha_fin"
                                    class="form-control"
                                    value="{{ request('fecha_fin') }}">
                            </div>

                            <!-- Botones -->
                            <div class="col-md-2">
                                    <!-- Filtrar -->
                                    <button
                                        type="submit"
                                        class="btn btn-primary w-100">
                                        🔍 Filtrar
                                    </button>
                            </div>
                            <div class="col-md-2">
                                    <!-- Limpiar -->
                                    <a href="{{ route('bitacora.secretaria_academica') }}"
                                       class="btn btn-dark w-100" >
                                        🧹 Limpiar
                                    </a>
                            </div>

                            <div class="col-md-4">




                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

    <!-- 📊 TABLA -->
    <div class="table-responsive">

        <table class="table table-bordered table-striped w-100">

            <thead class="table-light">
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
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->id_estudiante }}</td>
                        <td>{{ $item->tramite }}</td>
                        <td>{{ $item->accion }}</td>
                        <td>{{ $item->descripcion }}</td>

                        <!-- 📅 Fecha formateada -->
                        <td>
                            {{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="text-center">
                            No hay registros
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
