@extends('layouts.app-coordinador')

@section('content')
@vite(['resources/css/auditoria.css', 'resources/js/auditoria.js'])

<div class="container-fluid py-4">

        <!-- 📌 TÍTULO -->
    <div class="text-center mb-4">
        <h2 class="fw-bold">
            Auditoría del Sistema - Coordinador
        </h2>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
                        <!-- FORMULARIO DE CONSULTA -->
                        <form method="GET" action="{{ route('auditoria.coordinador') }}">
                             <div class="row align-items-end">
                                    <div class="col-md-4">

                                        <label class="form-label fw-bold">Fecha Inicial</label>
                                            <input
                                                type="date"
                                                name="fecha_inicio"
                                                class="form-control"
                                                value="{{ request('fecha_inicio') }}">
                                     </div>
                                     <div class="col-md-4">

                                        <label class="form-label fw-bold">Fecha Final</label>
                                            <input
                                                type="date"
                                                name="fecha_fin"
                                                class="form-control"
                                                value="{{ request('fecha_fin') }}">
                                    </div>

                                    <div class="col-md-2">

                                        <button type="submit" class="btn btn-primary w-100">
                                            🔍Buscar
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="{{ route('auditoria.coordinador') }}"
                                            class="btn btn-dark w-100">
                                                🧹Limpiar
                                        </a>

                                    </div>

                             </div>

                        </form>
        </div>
     </div>
                        <!-- TABLA DE RESULTADOS -->



                     <div class="table-responsive">
                            <table class="table table-bordered table-striped w-100">

                                <thead class="table-light">
                                    <tr>

                                        <th>No.</th>
                                        <th>Id Usuario</th>
                                        <th>Código Empleado</th>
                                        <th>Acción Realizada</th>
                                        <th>Detalle</th>
                                        <th>Módulo Afectado</th>
                                        <th>Fecha</th>

                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse($registros as $registro)

                                        <tr>

                                            <td>
                                                {{ $loop->iteration }}
                                            </td>

                                            <td>
                                                {{ $registro->id_usuario ?? '' }}
                                            </td>

                                            <td>
                                                {{ $registro->cod_empleado ?? '' }}
                                            </td>

                                            <td>
                                                {{ $registro->Accion_Realizada ?? '' }}
                                            </td>

                                            <td>
                                                {{ $registro->Detalle ?? '' }}
                                            </td>

                                            <td>
                                                {{ $registro->Modulo_Afectado ?? '' }}
                                            </td>

                                            <td>
                                                {{ $registro->fecha_y_hora ?? '' }}
                                            </td>

                                        </tr>

                                        @empty

                                        <tr>
                                                <td colspan="7" class="text-center">
                                                    No hay registros disponibles
                                                </td>
                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>



                            {{-- PAGINACIÓN --}}
                            <div style="margin-top: 15px;">
                                @if(method_exists($registros, 'links'))
                                    <div class="mt-3 d-flex justify-content-center">
                                        {{ $registros->links('pagination::bootstrap-5') }}
                                    </div>
                                @endif

                            </div>

                      </div>

</div>
@endsection
