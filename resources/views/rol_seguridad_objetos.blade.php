@extends('layouts.app-coordinador')

@section('hide_topbar', true)
@section('titulo', 'Módulos del Sistema')

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
                Módulos del Sistema
            </h2>

            <p class="security-subtitle mb-0">
                Administración de la disponibilidad de los módulos para estudiantes y Secretaría de tu carrera.
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


    {{-- LISTADO --}}

    <div class="card shadow border-0 security-card">

        <div class="card-header
                    security-header
                    d-flex
                    justify-content-between
                    align-items-center
                    flex-wrap
                    gap-2">

            <span class="fw-bold text-white">

                <i class="fas fa-cubes me-2"></i>

                Lista de Módulos

            </span>


            <span class="badge bg-light text-dark">

                Total módulos:
                {{ count($objetos) }}

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

                            <th>Módulo</th>

                            <th>Tipo</th>

                            <th>Estado General</th>

                            <th>Estudiante</th>

                            <th>Secretaría</th>

                            <th style="min-width: 310px;">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($objetos as $objeto)

                            @php

                                $tipoObjeto = strtoupper(
                                    trim(
                                        $objeto->tipo_objeto ?? ''
                                    )
                                );

                                $esPantalla =
                                    $tipoObjeto === 'PANTALLA';

                            @endphp


                            <tr>

                                {{-- ID --}}

                                <td>

                                    {{ $objeto->id_objeto }}

                                </td>


                                {{-- MÓDULO --}}

                                <td>

                                    <span class="fw-bold">

                                        {{ strtoupper(
                                            $objeto->nombre_objeto
                                            ?? 'SIN NOMBRE'
                                        ) }}

                                    </span>

                                </td>


                                {{-- TIPO --}}

                                <td>

                                    {{ $tipoObjeto ?: 'SIN TIPO' }}

                                </td>


                                {{-- ESTADO GENERAL --}}

                                <td>

                                    @if((int)$objeto->estado_activo === 1)

                                        <span class="badge bg-success">

                                            Activo

                                        </span>

                                    @else

                                        <span class="badge bg-danger">

                                            Inactivo

                                        </span>

                                    @endif

                                </td>


                                {{-- ESTUDIANTE --}}

                                <td>

                                    @if(!$esPantalla)

                                        <span class="badge bg-secondary">

                                            No aplica

                                        </span>


                                    @elseif((int)$objeto->estado_estudiante === 1)

                                        <span class="badge bg-success">

                                            Disponible

                                        </span>


                                    @else

                                        <span class="badge bg-warning text-dark">

                                            Mantenimiento

                                        </span>

                                    @endif

                                </td>


                                {{-- SECRETARÍA --}}

                                <td>

                                    @if(!$esPantalla)

                                        <span class="badge bg-secondary">

                                            No aplica

                                        </span>


                                    @elseif((int)$objeto->estado_secretario === 1)

                                        <span class="badge bg-success">

                                            Disponible

                                        </span>


                                    @else

                                        <span class="badge bg-warning text-dark">

                                            Mantenimiento

                                        </span>

                                    @endif

                                </td>


                                {{-- ACCIONES --}}

                                <td>

                                    @if(!$esPantalla)

                                        <button
                                            type="button"
                                            class="btn btn-secondary btn-sm"
                                            disabled
                                        >

                                            <i class="fas fa-lock me-1"></i>

                                            Protegido

                                        </button>


                                    @else

                                        <div class="d-flex gap-2 flex-wrap">


                                            {{-- =====================================
                                                 ESTUDIANTE
                                                 ===================================== --}}

                                            <form
                                                action="{{ route(
                                                    'seguridad.modulo.rol.estado',
                                                    $objeto->id_objeto
                                                ) }}"
                                                method="POST"
                                                class="js-confirm-submit"
                                                data-confirm="{{ (int)$objeto->estado_estudiante === 1
                                                    ? '¿Deseas poner este módulo en mantenimiento para los estudiantes de tu carrera?'
                                                    : '¿Deseas habilitar este módulo para los estudiantes de tu carrera?' }}"
                                            >

                                                @csrf
                                                @method('PUT')


                                                <input
                                                    type="hidden"
                                                    name="tipo_usuario"
                                                    value="ESTUDIANTE"
                                                >


                                                <input
                                                    type="hidden"
                                                    name="estado"
                                                    value="{{ (int)$objeto->estado_estudiante === 1 ? 0 : 1 }}"
                                                >


                                                @if((int)$objeto->estado_estudiante === 1)

                                                    <button
                                                        type="submit"
                                                        class="btn btn-warning btn-sm"
                                                    >

                                                        <i class="fas fa-user-graduate me-1"></i>

                                                        Mant. Estudiante

                                                    </button>


                                                @else

                                                    <button
                                                        type="submit"
                                                        class="btn btn-success btn-sm"
                                                    >

                                                        <i class="fas fa-user-graduate me-1"></i>

                                                        Habilitar Estudiante

                                                    </button>

                                                @endif

                                            </form>


                                            {{-- =====================================
                                                 SECRETARÍA
                                                 ===================================== --}}

                                            <form
                                                action="{{ route(
                                                    'seguridad.modulo.rol.estado',
                                                    $objeto->id_objeto
                                                ) }}"
                                                method="POST"
                                                class="js-confirm-submit"
                                                data-confirm="{{ (int)$objeto->estado_secretario === 1
                                                    ? '¿Deseas poner este módulo en mantenimiento para Secretaría de tu carrera?'
                                                    : '¿Deseas habilitar este módulo para Secretaría de tu carrera?' }}"
                                            >

                                                @csrf
                                                @method('PUT')


                                                <input
                                                    type="hidden"
                                                    name="tipo_usuario"
                                                    value="SECRETARIO"
                                                >


                                                <input
                                                    type="hidden"
                                                    name="estado"
                                                    value="{{ (int)$objeto->estado_secretario === 1 ? 0 : 1 }}"
                                                >


                                                @if((int)$objeto->estado_secretario === 1)

                                                    <button
                                                        type="submit"
                                                        class="btn btn-warning btn-sm"
                                                    >

                                                        <i class="fas fa-user-tie me-1"></i>

                                                        Mant. Secretaría

                                                    </button>


                                                @else

                                                    <button
                                                        type="submit"
                                                        class="btn btn-success btn-sm"
                                                    >

                                                        <i class="fas fa-user-tie me-1"></i>

                                                        Habilitar Secretaría

                                                    </button>

                                                @endif

                                            </form>

                                        </div>

                                    @endif

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center text-muted py-4"
                                >

                                    <i class="fas fa-info-circle me-1"></i>

                                    No hay módulos disponibles actualmente.

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
