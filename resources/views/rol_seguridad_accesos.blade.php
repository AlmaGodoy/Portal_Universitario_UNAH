@extends('layouts.app-coordinador')

@section('titulo', 'Permisos por Rol')
@section('hide_topbar', true)

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

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

        <div>

            <h2 class="security-title">
                Permisos por Rol
            </h2>

            <p class="security-subtitle mb-0">
                Asignación de permisos a los roles sobre los módulos disponibles para tu carrera.
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


    {{-- =========================================================
         ASIGNAR NUEVOS PERMISOS
         ========================================================= --}}

    <div class="card shadow border-0 security-card mb-4">

        <div class="card-header security-header">

            <span class="fw-bold text-white">

                <i class="fas fa-user-shield me-2"></i>
                Asignar Permisos

            </span>

        </div>


        <div class="card-body bg-white">

            <form
                action="{{ route('seguridad.acceso.store') }}"
                method="POST"
                class="js-confirm-submit"
                data-confirm="¿Deseas asignar los permisos seleccionados?"
            >

                @csrf


                <div class="row g-3">


                    {{-- =================================================
                         ROL
                         ================================================= --}}

                    <div class="col-md-4">

                        <label class="form-label fw-bold">
                            Rol:
                        </label>

                        <select
                            name="id_rol"
                            class="form-select"
                            required
                        >

                            <option value="">
                                - SELECCIONE ROL -
                            </option>


                            @foreach($roles as $rol)

                                <option
                                    value="{{ $rol->id_rol }}"
                                    {{ (string) old('id_rol') === (string) $rol->id_rol ? 'selected' : '' }}
                                >

                                    {{ strtoupper(
                                        $rol->nombre_rol ?? ''
                                    ) }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- =================================================
                         MÓDULO
                         ================================================= --}}

                    <div class="col-md-4">

                        <label class="form-label fw-bold">
                            Módulo:
                        </label>

                        <select
                            name="id_objeto"
                            class="form-select"
                            required
                        >

                            <option value="">
                                - SELECCIONE MÓDULO -
                            </option>


                            @foreach($objetos as $objeto)

                                <option
                                    value="{{ $objeto->id_objeto }}"
                                    {{ (string) old('id_objeto') === (string) $objeto->id_objeto ? 'selected' : '' }}
                                >

                                    {{ strtoupper(
                                        $objeto->nombre_objeto ?? ''
                                    ) }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- =================================================
                         SELECCIONAR TODOS
                         ================================================= --}}

                    <div class="col-md-4">

                        <label class="form-label fw-bold">
                            Selección rápida:
                        </label>

                        <div
                            class="border rounded px-3 py-2 bg-light d-flex align-items-center"
                            style="min-height: 38px;"
                        >

                            <div class="form-check mb-0">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="seleccionarTodosPermisos"
                                >

                                <label
                                    class="form-check-label fw-bold"
                                    for="seleccionarTodosPermisos"
                                >
                                    Seleccionar todos
                                </label>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         PERMISOS
                         ================================================= --}}

                    <div class="col-12">

                        <label class="form-label fw-bold">
                            Permisos:
                        </label>


                        <div class="border rounded p-3 bg-light">

                            <div class="row g-3">

                                @foreach($permisos as $permiso)

                                    <div class="col-md-3 col-sm-6">

                                        <div class="form-check">

                                            <input
                                                class="form-check-input permiso-checkbox"
                                                type="checkbox"
                                                name="id_permisos[]"
                                                value="{{ $permiso->id_permiso }}"
                                                id="permiso{{ $permiso->id_permiso }}"
                                                {{ in_array(
                                                    (string) $permiso->id_permiso,
                                                    array_map(
                                                        'strval',
                                                        old('id_permisos', [])
                                                    ),
                                                    true
                                                ) ? 'checked' : '' }}
                                            >

                                            <label
                                                class="form-check-label fw-bold"
                                                for="permiso{{ $permiso->id_permiso }}"
                                            >

                                                @php

                                                    $nombrePermisoFormulario =
                                                        strtolower(
                                                            $permiso->nombre_permiso
                                                            ?? ''
                                                        );

                                                @endphp


                                                @if($nombrePermisoFormulario === 'visualizar')

                                                    <i class="fas fa-eye me-1 text-info"></i>

                                                @elseif($nombrePermisoFormulario === 'guardar')

                                                    <i class="fas fa-save me-1 text-success"></i>

                                                @elseif($nombrePermisoFormulario === 'actualizar')

                                                    <i class="fas fa-edit me-1 text-warning"></i>

                                                @elseif($nombrePermisoFormulario === 'eliminar')

                                                    <i class="fas fa-trash me-1 text-danger"></i>

                                                @else

                                                    <i class="fas fa-key me-1"></i>

                                                @endif


                                                {{ strtoupper(
                                                    $permiso->nombre_permiso
                                                    ?? ''
                                                ) }}

                                            </label>

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        </div>

                        <small class="text-muted">
                            Puedes seleccionar uno, varios o todos los permisos.
                        </small>

                    </div>


                    {{-- =================================================
                         BOTÓN
                         ================================================= --}}

                    <div class="col-12">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i class="fas fa-save me-1"></i>
                            Guardar Permisos

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- =========================================================
         LISTA DE PERMISOS
         ========================================================= --}}

    <div class="card shadow border-0 security-card">

        <div class="card-header security-header
                    d-flex justify-content-between
                    align-items-center
                    flex-wrap gap-2">

            <span class="fw-bold text-white">

                <i class="fas fa-list me-2"></i>
                Permisos Asignados

            </span>


            <span class="badge bg-light text-dark">

                Total registros:
                {{ count($accesos) }}

            </span>

        </div>


        <div class="card-body bg-white">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>ID</th>

                            <th>Rol</th>

                            <th>Permiso</th>

                            <th>Módulo</th>

                            <th>Tipo</th>

                            <th>Fecha de Asignación</th>

                            <th>Estado</th>

                            <th style="min-width: 210px;">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($accesos as $acceso)

                            <tr>

                                {{-- ID --}}

                                <td>
                                    {{ $acceso->id_rol_permiso_carrera }}
                                </td>


                                {{-- ROL --}}

                                <td>

                                    <span class="fw-bold">

                                        {{ strtoupper(
                                            $acceso->nombre_rol
                                            ?? 'Sin rol'
                                        ) }}

                                    </span>

                                </td>


                                {{-- PERMISO --}}

                                <td>

                                    @php

                                        $nombrePermiso = strtolower(
                                            $acceso->nombre_permiso
                                            ?? ''
                                        );

                                    @endphp


                                    @if($nombrePermiso === 'visualizar')

                                        <span class="badge bg-info text-dark">

                                            <i class="fas fa-eye me-1"></i>
                                            Visualizar

                                        </span>

                                    @elseif($nombrePermiso === 'guardar')

                                        <span class="badge bg-success">

                                            <i class="fas fa-save me-1"></i>
                                            Guardar

                                        </span>

                                    @elseif($nombrePermiso === 'actualizar')

                                        <span class="badge bg-warning text-dark">

                                            <i class="fas fa-edit me-1"></i>
                                            Actualizar

                                        </span>

                                    @elseif($nombrePermiso === 'eliminar')

                                        <span class="badge bg-danger">

                                            <i class="fas fa-trash me-1"></i>
                                            Eliminar

                                        </span>

                                    @else

                                        <span class="badge bg-secondary">

                                            {{ strtoupper(
                                                $acceso->nombre_permiso
                                                ?? 'Sin permiso'
                                            ) }}

                                        </span>

                                    @endif

                                </td>


                                {{-- MÓDULO --}}

                                <td>

                                    {{ strtoupper(
                                        $acceso->nombre_objeto
                                        ?? 'Sin módulo'
                                    ) }}

                                </td>


                                {{-- TIPO --}}

                                <td>

                                    {{ strtoupper(
                                        $acceso->tipo_objeto
                                        ?? 'Sin tipo'
                                    ) }}

                                </td>


                                {{-- FECHA --}}

                                <td>

                                    {{ $acceso->fecha_asignacion
                                        ?? 'Sin fecha' }}

                                </td>


                                {{-- ESTADO --}}

                                <td>

                                    @if((int) ($acceso->estado_activo ?? 0) === 1)

                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Activo
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            <i class="fas fa-ban me-1"></i>
                                            Inactivo
                                        </span>

                                    @endif

                                </td>


                                {{-- ACCIONES --}}

                                <td>

                                    @php
                                        $permisoActivo =
                                            (int) ($acceso->estado_activo ?? 0)
                                            === 1;
                                    @endphp


                                    <div class="d-flex gap-2 flex-wrap">


                                        {{-- EDITAR --}}

                                        <button
                                            type="button"
                                            class="btn btn-primary btn-sm"
                                            data-toggle="modal"
                                            data-target="#modalEditarPermiso{{ $acceso->id_rol_permiso_carrera }}"
                                            {{ !$permisoActivo ? 'disabled' : '' }}
                                            title="{{ !$permisoActivo
                                                ? 'Activa el permiso antes de editarlo.'
                                                : 'Editar permiso'
                                            }}"
                                        >

                                            <i class="fas fa-edit me-1"></i>
                                            Editar

                                        </button>


                                        {{-- ACTIVAR / DESACTIVAR --}}

                                        <form
                                            action="{{ route(
                                                'seguridad.acceso.estado',
                                                $acceso->id_rol_permiso_carrera
                                            ) }}"
                                            method="POST"
                                            class="js-confirm-submit"
                                            data-confirm="{{ $permisoActivo
                                                ? '¿Seguro que deseas desactivar este permiso?'
                                                : '¿Seguro que deseas activar nuevamente este permiso?'
                                            }}"
                                        >

                                            @csrf
                                            @method('PUT')


                                            <input
                                                type="hidden"
                                                name="estado"
                                                value="{{ $permisoActivo ? 0 : 1 }}"
                                            >


                                            @if($permisoActivo)

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm"
                                                >

                                                    <i class="fas fa-ban me-1"></i>
                                                    Desactivar

                                                </button>

                                            @else

                                                <button
                                                    type="submit"
                                                    class="btn btn-success btn-sm"
                                                >

                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Activar

                                                </button>

                                            @endif

                                        </form>

                                    </div>

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center text-muted py-4"
                                >

                                    <i class="fas fa-info-circle me-1"></i>

                                    No hay permisos asignados actualmente para tu carrera.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


{{-- =============================================================
     MODALES PARA EDITAR PERMISOS
     ============================================================= --}}

@foreach($accesos as $acceso)

    <div
        class="modal fade"
        id="modalEditarPermiso{{ $acceso->id_rol_permiso_carrera }}"
        tabindex="-1"
        role="dialog"
        aria-hidden="true"
    >

        <div
            class="modal-dialog modal-lg"
            role="document"
        >

            <div class="modal-content shadow">


                <form
                    action="{{ route(
                        'seguridad.acceso.update',
                        $acceso->id_rol_permiso_carrera
                    ) }}"
                    method="POST"
                    class="js-confirm-submit"
                    data-confirm="¿Deseas actualizar este permiso?"
                >

                    @csrf
                    @method('PUT')


                    <div class="modal-header security-header">

                        <h5 class="modal-title text-white">

                            <i class="fas fa-edit me-2"></i>

                            Editar Permiso

                        </h5>


                        <button
                            type="button"
                            class="close text-white"
                            data-dismiss="modal"
                            aria-label="Cerrar"
                        >

                            <span aria-hidden="true">
                                &times;
                            </span>

                        </button>

                    </div>


                    <div class="modal-body bg-white">


                        <div class="alert alert-info">

                            <i class="fas fa-info-circle me-1"></i>

                            Modifica esta asignación de permiso.

                        </div>


                        <div class="row g-3">


                            {{-- ROL --}}

                            <div class="col-md-12">

                                <label class="form-label fw-bold">
                                    Rol:
                                </label>


                                <select
                                    name="id_rol"
                                    class="form-select"
                                    required
                                >

                                    @foreach($roles as $rol)

                                        <option
                                            value="{{ $rol->id_rol }}"
                                            {{ (int) $acceso->id_rol === (int) $rol->id_rol ? 'selected' : '' }}
                                        >

                                            {{ strtoupper(
                                                $rol->nombre_rol
                                                ?? ''
                                            ) }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- PERMISO --}}

                            <div class="col-md-12">

                                <label class="form-label fw-bold">
                                    Permiso:
                                </label>


                                <select
                                    name="id_permiso"
                                    class="form-select"
                                    required
                                >

                                    @foreach($permisos as $permiso)

                                        <option
                                            value="{{ $permiso->id_permiso }}"
                                            {{ (int) $acceso->id_permiso === (int) $permiso->id_permiso ? 'selected' : '' }}
                                        >

                                            {{ strtoupper(
                                                $permiso->nombre_permiso
                                                ?? ''
                                            ) }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- MÓDULO --}}

                            <div class="col-md-12">

                                <label class="form-label fw-bold">
                                    Módulo:
                                </label>


                                <select
                                    name="id_objeto"
                                    class="form-select"
                                    required
                                >

                                    @foreach($objetos as $objeto)

                                        <option
                                            value="{{ $objeto->id_objeto }}"
                                            {{ (int) $acceso->id_objeto === (int) $objeto->id_objeto ? 'selected' : '' }}
                                        >

                                            {{ strtoupper(
                                                $objeto->nombre_objeto
                                                ?? ''
                                            ) }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                        </div>

                    </div>


                    <div class="modal-footer bg-white">

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-dismiss="modal"
                        >

                            Cancelar

                        </button>


                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="fas fa-save me-1"></i>

                            Actualizar Permiso

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endforeach


{{-- =============================================================
     SELECCIONAR TODOS LOS PERMISOS
     ============================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    const seleccionarTodos =
        document.getElementById(
            'seleccionarTodosPermisos'
        );

    const permisos =
        document.querySelectorAll(
            '.permiso-checkbox'
        );


    if (!seleccionarTodos) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | MARCAR / DESMARCAR TODOS
    |--------------------------------------------------------------------------
    */

    seleccionarTodos.addEventListener(
        'change',
        function () {

            permisos.forEach(
                function (permiso) {

                    permiso.checked =
                        seleccionarTodos.checked;

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR "SELECCIONAR TODOS"
    |--------------------------------------------------------------------------
    */

    function actualizarSeleccionTodos() {

        if (permisos.length === 0) {

            seleccionarTodos.checked =
                false;

            seleccionarTodos.indeterminate =
                false;

            return;
        }


        const seleccionados =
            Array.from(permisos)
                .filter(function (permiso) {

                    return permiso.checked;

                })
                .length;


        seleccionarTodos.checked =
            seleccionados === permisos.length;


        seleccionarTodos.indeterminate =
            seleccionados > 0
            && seleccionados < permisos.length;
    }


    permisos.forEach(
        function (permiso) {

            permiso.addEventListener(
                'change',
                actualizarSeleccionTodos
            );

        }
    );


    actualizarSeleccionTodos();

});

</script>

@endsection
