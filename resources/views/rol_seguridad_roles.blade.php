@extends('layouts.app-coordinador')

@section('hide_topbar', true)
@section('titulo', 'Roles del Sistema')

@section('content')

<div class="container py-4 security-page">

    {{-- MENSAJES --}}

    @if(session('status'))

        <div class="alert alert-success shadow-sm">
            {{ session('status') }}
        </div>

    @endif


    @if($errors->any())

        <div class="alert alert-danger shadow-sm">
            {{ $errors->first() }}
        </div>

    @endif


    {{-- ENCABEZADO --}}

    <div class="mb-4
                d-flex
                justify-content-between
                align-items-center
                flex-wrap
                gap-2">

        <div>

            <h2 class="security-title">
                Roles del Sistema
            </h2>

            <p class="security-subtitle mb-0">
                Consulta y administración del estado de los roles correspondientes a tu carrera.
            </p>

        </div>


        <a
            href="{{ route('seguridad.index') }}"
            class="btn btn-outline-secondary"
        >

            <i class="fas fa-arrow-left me-1"></i>

            Volver a Seguridad

        </a>

    </div>


    {{-- FILTROS --}}

    <div class="card
                shadow
                border-0
                security-card
                mb-4">

        <div class="card-header security-header">

            <span class="fw-bold text-white">

                <i class="fas fa-filter me-2"></i>

                Filtros de Roles

            </span>

        </div>


        <div class="card-body bg-white">

            <form
                method="GET"
                action="{{ route('seguridad.roles') }}"
            >

                <div class="row g-3">


                    {{-- BUSCAR --}}

                    <div class="col-md-6 col-lg-5">

                        <label class="form-label fw-bold">

                            Buscar por nombre o descripción

                        </label>


                        <input
                            type="text"
                            name="buscar"
                            class="form-control"
                            placeholder="Ej. estudiante, secretario..."
                            value="{{ $filtros['buscar'] ?? '' }}"
                        >

                    </div>


                    {{-- ESTADO --}}

                    <div class="col-md-6 col-lg-3">

                        <label class="form-label fw-bold">

                            Estado en mi carrera

                        </label>


                        <select
                            name="estado_activo"
                            class="form-select"
                        >

                            <option value="">
                                Todos
                            </option>

                            <option
                                value="1"
                                {{ (string)($filtros['estado_activo'] ?? '') === '1' ? 'selected' : '' }}
                            >
                                Activo
                            </option>

                            <option
                                value="0"
                                {{ (string)($filtros['estado_activo'] ?? '') === '0' ? 'selected' : '' }}
                            >
                                Inactivo
                            </option>

                        </select>

                    </div>


                    {{-- BOTONES --}}

                    <div class="col-12
                                d-flex
                                gap-2
                                flex-wrap">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="fas fa-search me-1"></i>

                            Filtrar

                        </button>


                        <a
                            href="{{ route('seguridad.roles') }}"
                            class="btn btn-outline-secondary"
                        >

                            <i class="fas fa-eraser me-1"></i>

                            Limpiar filtros

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- LISTA DE ROLES --}}

    <div class="card
                shadow
                border-0
                security-card">

        <div class="card-header
                    security-header
                    d-flex
                    justify-content-between
                    align-items-center
                    flex-wrap
                    gap-2">

            <span class="fw-bold text-white">

                <i class="fas fa-user-tag me-2"></i>

                Lista de Roles

            </span>


            <span class="badge bg-light text-dark">

                Total roles:
                {{ count($roles) }}

            </span>

        </div>


        <div class="card-body bg-white">

            <div class="table-responsive">

                <table class="table
                              table-bordered
                              table-hover
                              align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>ID</th>

                            <th>Rol</th>

                            <th>Descripción</th>

                            <th>Estado en mi Carrera</th>

                            <th class="col-acciones">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($roles as $rol)

                            <tr>

                                {{-- ID --}}

                                <td>
                                    {{ $rol->id_rol }}
                                </td>


                                {{-- ROL --}}

                                <td>

                                    <span class="fw-bold">

                                        {{ strtoupper(
                                            $rol->nombre_rol
                                            ?? 'SIN ROL'
                                        ) }}

                                    </span>

                                </td>


                                {{-- DESCRIPCIÓN --}}

                                <td>

                                    {{ $rol->descripcion
                                        ?? 'Sin descripción' }}

                                </td>


                                {{-- ESTADO --}}

                                <td>

                                    @if((int)$rol->estado_carrera === 1)

                                        <span class="badge bg-success">

                                            Activo

                                        </span>

                                    @else

                                        <span class="badge bg-danger">

                                            Inactivo

                                        </span>

                                    @endif

                                </td>


                                {{-- ACCIONES --}}

                                <td>

                                    {{-- COORDINADOR PROTEGIDO --}}

                                    @if((int)$rol->id_rol === 4)

                                        <button
                                            type="button"
                                            class="btn btn-secondary btn-sm"
                                            disabled
                                            title="El rol Coordinador está protegido"
                                        >

                                            <i class="fas fa-lock me-1"></i>

                                            Protegido

                                        </button>


                                    {{-- ESTUDIANTE / SECRETARIO --}}

                                    @else

                                        <form
                                            action="{{ route(
                                                'seguridad.rol.estado',
                                                $rol->id_rol
                                            ) }}"
                                            method="POST"
                                            class="js-confirm-submit"
                                            data-confirm="{{ (int)$rol->estado_carrera === 1
                                                ? '¿Deseas desactivar este rol para tu carrera?'
                                                : '¿Deseas activar este rol para tu carrera?' }}"
                                        >

                                            @csrf
                                            @method('PUT')


                                            <input
                                                type="hidden"
                                                name="estado_activo"
                                                value="{{ (int)$rol->estado_carrera === 1 ? 0 : 1 }}"
                                            >


                                            @if((int)$rol->estado_carrera === 1)

                                                <button
                                                    type="submit"
                                                    class="btn btn-warning btn-sm"
                                                >

                                                    <i class="fas fa-ban me-1"></i>

                                                    Desactivar

                                                </button>

                                            @else

                                                <button
                                                    type="submit"
                                                    class="btn btn-success btn-sm"
                                                >

                                                    <i class="fas fa-check me-1"></i>

                                                    Activar

                                                </button>

                                            @endif

                                        </form>

                                    @endif

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center text-muted py-4"
                                >

                                    <i class="fas fa-info-circle me-1"></i>

                                    No hay roles disponibles con los filtros aplicados.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection
