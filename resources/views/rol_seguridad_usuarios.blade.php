@extends('layouts.app-coordinador')

@section('hide_topbar', true)
@section('titulo', 'Usuarios de mi Carrera')

@section('content')

<div class="container py-4 security-page">

    {{-- =========================================================
         MENSAJES
         ========================================================= --}}

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


    {{-- =========================================================
         ENCABEZADO
         ========================================================= --}}

    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">

        <div>
            <h2 class="security-title">
                Usuarios de mi Carrera
            </h2>

            <p class="security-subtitle mb-0">
                Consulta y administración del estado de las cuentas correspondientes a tu carrera.
            </p>
        </div>


        <a href="{{ route('seguridad.index') }}"
           class="btn btn-outline-secondary">

            <i class="fas fa-arrow-left me-1"></i>
            Volver a Seguridad

        </a>

    </div>


    {{-- =========================================================
         FILTROS DE BÚSQUEDA
         ========================================================= --}}

    <div class="card shadow border-0 security-card mb-4">

        <div class="card-header security-header">

            <span class="fw-bold text-white">

                <i class="fas fa-filter me-2"></i>
                Filtros de Búsqueda

            </span>

        </div>


        <div class="card-body bg-white">

            <form method="GET"
                  action="{{ route('seguridad.usuarios') }}">

                <div class="row g-3">


                    {{-- =====================================================
                         CARRERA DEL COORDINADOR
                         ===================================================== --}}

                    <div class="col-12">

                        <label class="form-label fw-bold">
                            Carrera asignada
                        </label>

                        @php

                            $carrerasCollection = collect($carreras);

                            $rolesCollection = collect($roles);

                            $carreraActualObj = $carrerasCollection
                                ->firstWhere(
                                    'id_carrera',
                                    $idCarreraActual
                                );

                            $nombreCarreraActual =
                                $carreraActualObj->nombre_carrera
                                ?? 'Carrera no identificada';

                        @endphp


                        <div
                            class="form-control input-highlight"
                            style="
                                height: auto;
                                min-height: 46px;
                                white-space: normal;
                                line-height: 1.35;
                            "
                        >

                            {{ strtoupper($nombreCarreraActual) }}

                        </div>

                    </div>


                    {{-- =====================================================
                         BUSCAR
                         ===================================================== --}}

                    <div class="col-md-6 col-lg-3">

                        <label class="form-label fw-bold">
                            Buscar
                        </label>

                        <input
                            type="text"
                            name="buscar"
                            class="form-control"
                            placeholder="Nombre o correo"
                            value="{{ $filtros['buscar'] ?? '' }}"
                        >

                    </div>


                    {{-- =====================================================
                         TIPO DE USUARIO
                         ===================================================== --}}

                    <div class="col-md-6 col-lg-3">

                        <label class="form-label fw-bold">
                            Tipo
                        </label>

                        <select
                            name="tipo_usuario"
                            class="form-select"
                        >

                            <option value="">
                                Todos
                            </option>


                            <option
                                value="estudiante"
                                {{ ($filtros['tipo_usuario'] ?? '') === 'estudiante' ? 'selected' : '' }}
                            >
                                Estudiante
                            </option>


                            <option
                                value="coordinador"
                                {{ ($filtros['tipo_usuario'] ?? '') === 'coordinador' ? 'selected' : '' }}
                            >
                                Coordinador
                            </option>


                            <option
                                value="secretario"
                                {{ ($filtros['tipo_usuario'] ?? '') === 'secretario' ? 'selected' : '' }}
                            >
                                Secretario / Secretaria
                            </option>

                        </select>

                    </div>


                    {{-- =====================================================
                         ROL
                         ===================================================== --}}

                    <div class="col-md-6 col-lg-3">

                        <label class="form-label fw-bold">
                            Rol
                        </label>

                        <select
                            name="id_rol"
                            class="form-select"
                        >

                            <option value="">
                                Todos
                            </option>


                            @foreach($rolesCollection as $rol)

                                <option
                                    value="{{ $rol->id_rol }}"
                                    {{ (string)($filtros['id_rol'] ?? '') === (string)$rol->id_rol ? 'selected' : '' }}
                                >

                                    {{ strtoupper(
                                        $rol->nombre_rol ?? 'ROL'
                                    ) }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- =====================================================
                         ESTADO
                         ===================================================== --}}

                    <div class="col-md-6 col-lg-3">

                        <label class="form-label fw-bold">
                            Estado
                        </label>

                        <select
                            name="estado_cuenta"
                            class="form-select"
                        >

                            <option value="">
                                Todos
                            </option>


                            <option
                                value="1"
                                {{ (string)($filtros['estado_cuenta'] ?? '') === '1' ? 'selected' : '' }}
                            >
                                Activo
                            </option>


                            <option
                                value="0"
                                {{ (string)($filtros['estado_cuenta'] ?? '') === '0' ? 'selected' : '' }}
                            >
                                Inactivo
                            </option>

                        </select>

                    </div>


                    {{-- =====================================================
                         BOTONES
                         ===================================================== --}}

                    <div class="col-12 d-flex gap-2 flex-wrap">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="fas fa-search me-1"></i>
                            Filtrar

                        </button>


                        <a
                            href="{{ route('seguridad.usuarios') }}"
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


    {{-- =========================================================
         LISTADO DE USUARIOS
         ========================================================= --}}

    <div class="card shadow border-0 security-card">

        <div class="card-header security-header
                    d-flex justify-content-between
                    align-items-center
                    flex-wrap gap-2">

            <span class="fw-bold text-white">

                <i class="fas fa-users me-2"></i>
                Lista de Usuarios

            </span>


            <span class="badge bg-light text-dark">

                Total registros:
                {{ $usuarios->total() }}

            </span>

        </div>


        <div class="card-body bg-white">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>#</th>

                            <th>Nombre</th>

                            <th>Correo</th>

                            <th>Tipo</th>

                            <th>Rol</th>

                            <th>Carrera</th>

                            <th>Estado Cuenta</th>

                            <th class="col-acciones">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($usuarios as $usuario)

                            @php

                                $carreraUsuario = $carrerasCollection
                                    ->firstWhere(
                                        'id_carrera',
                                        $usuario->id_carrera ?? null
                                    );

                                $nombreCarreraUsuario =
                                    $carreraUsuario->nombre_carrera
                                    ?? 'Sin carrera';

                            @endphp


                            <tr>

                                {{-- ID --}}

                                <td>
                                    {{ $usuario->id_usuario }}
                                </td>


                                {{-- NOMBRE --}}

                                <td>

                                    {{ $usuario->nombre_persona
                                        ?? 'Sin nombre' }}

                                </td>


                                {{-- CORREO --}}

                                <td>

                                    {{ $usuario->correo_institucional
                                        ?? 'Sin correo' }}

                                </td>


                                {{-- TIPO --}}

                                <td>

                                    {{ strtoupper(
                                        $usuario->tipo_usuario
                                        ?? 'Sin tipo'
                                    ) }}

                                </td>


                                {{-- ROL --}}

                                <td>

                                    {{ strtoupper(
                                        $usuario->nombre_rol
                                        ?? 'Sin rol'
                                    ) }}

                                </td>


                                {{-- CARRERA --}}

                                <td>

                                    {{ strtoupper(
                                        $nombreCarreraUsuario
                                    ) }}

                                </td>


                                {{-- ESTADO --}}

                                <td>

                                    @if((int)($usuario->estado_cuenta ?? 0) === 1)

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

                                    <form
                                        action="{{ route(
                                            'seguridad.usuario.estado',
                                            $usuario->id_usuario
                                        ) }}"
                                        method="POST"
                                        class="js-confirm-submit"
                                        data-confirm="¿Deseas actualizar el estado de este usuario?"
                                    >

                                        @csrf
                                        @method('PUT')


                                        <input
                                            type="hidden"
                                            name="estado_cuenta"
                                            value="{{ (int)($usuario->estado_cuenta ?? 0) === 1 ? 0 : 1 }}"
                                        >


                                        <button
                                            type="submit"
                                            class="btn btn-sm {{ (int)($usuario->estado_cuenta ?? 0) === 1 ? 'btn-warning' : 'btn-success' }}"
                                        >

                                            @if((int)($usuario->estado_cuenta ?? 0) === 1)

                                                <i class="fas fa-user-slash me-1"></i>
                                                Desactivar

                                            @else

                                                <i class="fas fa-user-check me-1"></i>
                                                Activar

                                            @endif

                                        </button>

                                    </form>

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center text-muted py-4"
                                >

                                    <i class="fas fa-info-circle me-1"></i>

                                    No hay usuarios registrados para tu carrera con los filtros aplicados.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- =================================================
                 PAGINACIÓN
                 ================================================= --}}

            @if($usuarios->hasPages())

                <div class="mt-3">

                    {{ $usuarios->links() }}

                </div>

            @endif

        </div>

    </div>

</div>

@endsection
