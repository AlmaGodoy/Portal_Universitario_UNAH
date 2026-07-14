@extends('layouts.app-secretaria')

@section('title', 'Auditoría Institucional')
@section('titulo', 'Auditoría Institucional')

@section('content')

@vite([
    'resources/css/auditoria_secretaria_academica.css',
    'resources/js/auditoria_secretaria_academica.js'
])

@php
    /*
    |--------------------------------------------------------------------------
    | NORMALIZACIÓN DE RESULTADOS
    |--------------------------------------------------------------------------
    */

    $fuenteAuditorias =
        $auditorias
        ?? $registros
        ?? collect();

    $esPaginadorCompleto =
        $fuenteAuditorias instanceof
        \Illuminate\Pagination\LengthAwarePaginator;

    $esPaginadorSimple =
        $fuenteAuditorias instanceof
        \Illuminate\Pagination\Paginator;

    $esPaginador =
        $esPaginadorCompleto
        || $esPaginadorSimple;

    $itemsAuditoria = $esPaginador
        ? collect($fuenteAuditorias->items())
        : (
            $fuenteAuditorias instanceof
            \Illuminate\Support\Collection
                ? $fuenteAuditorias
                : collect($fuenteAuditorias)
        );

    $totalRegistros = $esPaginadorCompleto
        ? $fuenteAuditorias->total()
        : $itemsAuditoria->count();

    $carrerasDisponibles =
        $carreras
        ?? collect();

    $rolesDisponibles =
        $roles
        ?? collect([
            (object) [
                'id_rol' => 1,
                'nombre_rol' => 'Secretaría Académica',
            ],
            (object) [
                'id_rol' => 2,
                'nombre_rol' => 'Estudiante',
            ],
            (object) [
                'id_rol' => 4,
                'nombre_rol' => 'Coordinador de Carrera',
            ],
            (object) [
                'id_rol' => 5,
                'nombre_rol' => 'Secretaría de Carrera',
            ],
        ]);

    $objetosDisponibles =
        $objetos
        ?? collect();

    $operacionesDisponibles =
        $operaciones
        ?? [
            'INSERT' => 'Creación',
            'UPDATE' => 'Actualización',
            'DELETE' => 'Eliminación',
            'LOGIN' => 'Inicio de sesión',
            'LOGOUT' => 'Cierre de sesión',
            'OTRA' => 'Otra operación',
        ];

    $nivelesDisponibles =
        $niveles
        ?? [
            'INFO' => 'Información',
            'ADVERTENCIA' => 'Advertencia',
            'ERROR' => 'Error',
            'SEGURIDAD' => 'Seguridad',
        ];

    $fechaInicioActual = request(
        'fecha_inicio',
        $filtros['fecha_inicio']
            ?? now()->subMonth()->toDateString()
    );

    $fechaFinActual = request(
        'fecha_fin',
        $filtros['fecha_fin']
            ?? now()->toDateString()
    );
@endphp

<div class="sa-aud-page">

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}
    <section class="sa-aud-hero">

        <div class="sa-aud-hero__content">

            <div class="sa-aud-hero__information">

                <span class="sa-aud-hero__eyebrow">
                    Secretaría Académica
                </span>

                <h1 class="sa-aud-hero__title">
                    Auditoría Institucional
                </h1>

                <p class="sa-aud-hero__description">
                    Consulta las operaciones y cambios relevantes
                    registrados en todas las carreras y áreas del sistema.
                </p>

            </div>

            <div class="sa-aud-hero__actions">

                <span class="sa-aud-global-badge">
                    <i class="fas fa-globe-americas"></i>
                    Acceso global
                </span>

                <a
                    href="{{ url()->previous() }}"
                    class="sa-aud-button sa-aud-button--back"
                >
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver</span>
                </a>

            </div>

        </div>

    </section>

    {{-- =====================================================
         MENSAJES
    ====================================================== --}}
    @if(session('success'))

        <div
            class="sa-aud-alert sa-aud-alert--success"
            data-sa-aud-auto-close
        >
            <i class="fas fa-check-circle"></i>

            <span>
                {{ session('success') }}
            </span>
        </div>

    @endif

    @if(session('error'))

        <div class="sa-aud-alert sa-aud-alert--danger">

            <i class="fas fa-exclamation-triangle"></i>

            <span>
                {{ session('error') }}
            </span>

        </div>

    @endif

    @if($errors->any())

        <div class="sa-aud-alert sa-aud-alert--danger">

            <i class="fas fa-circle-exclamation"></i>

            <div>

                <strong>
                    No se pudo realizar la búsqueda.
                </strong>

                <ul class="sa-aud-alert__list">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </div>

    @endif

    {{-- =====================================================
         RESUMEN
    ====================================================== --}}
    <section class="sa-aud-summary">

        <article class="sa-aud-summary-card">

            <span class="sa-aud-summary-card__icon">
                <i class="fas fa-clipboard-check"></i>
            </span>

            <div>

                <span class="sa-aud-summary-card__label">
                    Registros encontrados
                </span>

                <strong class="sa-aud-summary-card__value">
                    {{ $totalRegistros }}
                </strong>

            </div>

        </article>

        <article class="sa-aud-summary-card">

            <span class="sa-aud-summary-card__icon">
                <i class="fas fa-graduation-cap"></i>
            </span>

            <div>

                <span class="sa-aud-summary-card__label">
                    Carreras disponibles
                </span>

                <strong class="sa-aud-summary-card__value">
                    {{ $carrerasDisponibles->count() }}
                </strong>

            </div>

        </article>

        <article class="sa-aud-summary-card">

            <span class="sa-aud-summary-card__icon">
                <i class="fas fa-shield-halved"></i>
            </span>

            <div>

                <span class="sa-aud-summary-card__label">
                    Alcance de consulta
                </span>

                <strong
                    class="
                        sa-aud-summary-card__value
                        sa-aud-summary-card__value--text
                    "
                >
                    Global
                </strong>

            </div>

        </article>

        <article class="sa-aud-summary-card">

            <span class="sa-aud-summary-card__icon">
                <i class="fas fa-calendar-days"></i>
            </span>

            <div>

                <span class="sa-aud-summary-card__label">
                    Período consultado
                </span>

                <strong
                    class="
                        sa-aud-summary-card__value
                        sa-aud-summary-card__value--date
                    "
                >
                    {{ \Carbon\Carbon::parse(
                        $fechaInicioActual
                    )->format('d/m/Y') }}

                    —

                    {{ \Carbon\Carbon::parse(
                        $fechaFinActual
                    )->format('d/m/Y') }}
                </strong>

            </div>

        </article>

    </section>

    {{-- =====================================================
         FILTROS
    ====================================================== --}}
    <section class="sa-aud-card">

        <header class="sa-aud-card__header">

            <div class="sa-aud-card__heading">

                <span class="sa-aud-card__icon">
                    <i class="fas fa-filter"></i>
                </span>

                <div>

                    <h2 class="sa-aud-card__title">
                        Filtros de búsqueda
                    </h2>

                    <p class="sa-aud-card__subtitle">
                        Selecciona uno o varios criterios para consultar
                        la actividad registrada en toda la institución.
                    </p>

                </div>

            </div>

        </header>

        <div class="sa-aud-card__body">

            <form
                id="formAuditoriaSecretariaAcademica"
                method="GET"
                action="{{ route('auditoria.administrativa') }}"
                autocomplete="off"
            >

                <div class="sa-aud-filter-grid">

                    {{-- FECHA INICIAL --}}
                    <div class="sa-aud-field">

                        <label
                            for="fecha_inicio"
                            class="sa-aud-label"
                        >
                            Fecha inicial
                        </label>

                        <input
                            type="date"
                            id="fecha_inicio"
                            name="fecha_inicio"
                            class="sa-aud-input"
                            value="{{ $fechaInicioActual }}"
                            required
                        >

                        <span
                            id="errorFechaInicioAuditoriaSA"
                            class="sa-aud-error"
                        >
                            Debe seleccionar la fecha inicial.
                        </span>

                    </div>

                    {{-- FECHA FINAL --}}
                    <div class="sa-aud-field">

                        <label
                            for="fecha_fin"
                            class="sa-aud-label"
                        >
                            Fecha final
                        </label>

                        <input
                            type="date"
                            id="fecha_fin"
                            name="fecha_fin"
                            class="sa-aud-input"
                            value="{{ $fechaFinActual }}"
                            required
                        >

                        <span
                            id="errorFechaFinAuditoriaSA"
                            class="sa-aud-error"
                        >
                            La fecha final no puede ser anterior
                            a la fecha inicial.
                        </span>

                    </div>

                    {{-- CARRERA --}}
                    <div class="sa-aud-field">

                        <label
                            for="id_carrera"
                            class="sa-aud-label"
                        >
                            Carrera
                        </label>

                        <select
                            id="id_carrera"
                            name="id_carrera"
                            class="sa-aud-select"
                            data-sa-aud-optional
                        >

                            <option value="">
                                Todas las carreras
                            </option>

                            @foreach($carrerasDisponibles as $carrera)

                                <option
                                    value="{{ $carrera->id_carrera }}"
                                    @selected(
                                        (string) request('id_carrera')
                                        ===
                                        (string) $carrera->id_carrera
                                    )
                                >
                                    {{ $carrera->nombre_carrera
                                        ?? 'Carrera #'
                                            . $carrera->id_carrera }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- ID DE USUARIO --}}
                    <div class="sa-aud-field">

                        <label
                            for="id_usuario"
                            class="sa-aud-label"
                        >
                            ID de usuario
                        </label>

                        <input
                            type="number"
                            id="id_usuario"
                            name="id_usuario"
                            class="sa-aud-input"
                            value="{{ request('id_usuario') }}"
                            min="1"
                            step="1"
                            placeholder="Ej. 144"
                            data-sa-aud-optional
                        >

                        <span
                            id="errorUsuarioAuditoriaSA"
                            class="sa-aud-error"
                        >
                            Ingrese un ID de usuario válido.
                        </span>

                    </div>

                    {{-- ROL --}}
                    <div class="sa-aud-field">

                        <label
                            for="id_rol"
                            class="sa-aud-label"
                        >
                            Rol
                        </label>

                        <select
                            id="id_rol"
                            name="id_rol"
                            class="sa-aud-select"
                            data-sa-aud-optional
                        >

                            <option value="">
                                Todos los roles
                            </option>

                            @foreach($rolesDisponibles as $rol)

                                <option
                                    value="{{ $rol->id_rol }}"
                                    @selected(
                                        (string) request('id_rol')
                                        ===
                                        (string) $rol->id_rol
                                    )
                                >
                                    {{ $rol->nombre_rol
                                        ?? 'Rol #' . $rol->id_rol }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- OBJETO --}}
                    <div class="sa-aud-field">

                        <label
                            for="id_objeto"
                            class="sa-aud-label"
                        >
                            Objeto
                        </label>

                        <select
                            id="id_objeto"
                            name="id_objeto"
                            class="sa-aud-select"
                            data-sa-aud-optional
                        >

                            <option value="">
                                Todos los objetos
                            </option>

                            @foreach($objetosDisponibles as $objeto)

                                <option
                                    value="{{ $objeto->id_objeto }}"
                                    @selected(
                                        (string) request('id_objeto')
                                        ===
                                        (string) $objeto->id_objeto
                                    )
                                >
                                    {{ $objeto->nombre_objeto
                                        ?? 'Objeto #'
                                            . $objeto->id_objeto }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- TRÁMITE --}}
                    <div class="sa-aud-field">

                        <label
                            for="id_tramite"
                            class="sa-aud-label"
                        >
                            Número de trámite
                        </label>

                        <input
                            type="number"
                            id="id_tramite"
                            name="id_tramite"
                            class="sa-aud-input"
                            value="{{ request('id_tramite') }}"
                            min="1"
                            step="1"
                            placeholder="Ej. 146"
                            data-sa-aud-optional
                        >

                        <span
                            id="errorTramiteAuditoriaSA"
                            class="sa-aud-error"
                        >
                            Ingrese un número de trámite válido.
                        </span>

                    </div>

                    {{-- OPERACIÓN --}}
                    <div class="sa-aud-field">

                        <label
                            for="operacion"
                            class="sa-aud-label"
                        >
                            Operación
                        </label>

                        <select
                            id="operacion"
                            name="operacion"
                            class="sa-aud-select"
                            data-sa-aud-optional
                        >

                            <option value="">
                                Todas las operaciones
                            </option>

                            @foreach(
                                $operacionesDisponibles
                                as $codigoOperacion => $nombreOperacion
                            )

                                <option
                                    value="{{ $codigoOperacion }}"
                                    @selected(
                                        request('operacion')
                                        === $codigoOperacion
                                    )
                                >
                                    {{ $nombreOperacion }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- TABLA AFECTADA --}}
                    <div class="sa-aud-field">

                        <label
                            for="tabla_afectada"
                            class="sa-aud-label"
                        >
                            Tabla afectada
                        </label>

                        <input
                            type="text"
                            id="tabla_afectada"
                            name="tabla_afectada"
                            class="sa-aud-input"
                            value="{{ request('tabla_afectada') }}"
                            maxlength="100"
                            placeholder="Ej. tbl_tramite"
                            data-sa-aud-optional
                        >

                    </div>

                    {{-- ACCIÓN --}}
                    <div class="sa-aud-field">

                        <label
                            for="accion"
                            class="sa-aud-label"
                        >
                            Acción
                        </label>

                        <input
                            type="text"
                            id="accion"
                            name="accion"
                            class="sa-aud-input"
                            value="{{ request('accion') }}"
                            maxlength="100"
                            placeholder="Ej. ACTUALIZAR_ESTADO"
                            data-sa-aud-optional
                        >

                    </div>

                    {{-- NIVEL --}}
                    <div class="sa-aud-field">

                        <label
                            for="nivel"
                            class="sa-aud-label"
                        >
                            Nivel
                        </label>

                        <select
                            id="nivel"
                            name="nivel"
                            class="sa-aud-select"
                            data-sa-aud-optional
                        >

                            <option value="">
                                Todos los niveles
                            </option>

                            @foreach(
                                $nivelesDisponibles
                                as $codigoNivel => $nombreNivel
                            )

                                <option
                                    value="{{ $codigoNivel }}"
                                    @selected(
                                        request('nivel')
                                        === $codigoNivel
                                    )
                                >
                                    {{ $nombreNivel }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- REGISTROS --}}
                    <div class="sa-aud-field">

                        <label
                            for="per_page"
                            class="sa-aud-label"
                        >
                            Registros
                        </label>

                        <select
                            id="per_page"
                            name="per_page"
                            class="sa-aud-select"
                        >

                            @foreach([10, 20, 50, 100] as $cantidad)

                                <option
                                    value="{{ $cantidad }}"
                                    @selected(
                                        (int) request(
                                            'per_page',
                                            $filtros['per_page'] ?? 20
                                        ) === $cantidad
                                    )
                                >
                                    {{ $cantidad }} por página
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="sa-aud-filter-actions">

                    <button
                        type="submit"
                        id="btnBuscarAuditoriaSA"
                        class="sa-aud-button sa-aud-button--search"
                    >
                        <i class="fas fa-search"></i>
                        <span>Buscar</span>
                    </button>

                    <a
                        href="{{ route('auditoria.administrativa') }}"
                        class="sa-aud-button sa-aud-button--clear"
                    >
                        <i class="fas fa-eraser"></i>
                        <span>Limpiar filtros</span>
                    </a>

                </div>

            </form>

        </div>

    </section>

    {{-- =====================================================
         RESULTADOS
    ====================================================== --}}
    <section class="sa-aud-card">

        <header
            class="
                sa-aud-card__header
                sa-aud-card__header--between
            "
        >

            <div class="sa-aud-card__heading">

                <span class="sa-aud-card__icon">
                    <i class="fas fa-clipboard-list"></i>
                </span>

                <div>

                    <h2 class="sa-aud-card__title">
                        Registros de auditoría
                    </h2>

                    <p class="sa-aud-card__subtitle">
                        Se encontraron

                        <strong>
                            {{ $totalRegistros }}
                        </strong>

                        registros en el sistema.
                    </p>

                </div>

            </div>

            <span class="sa-aud-context">
                <i class="fas fa-building-shield"></i>
                Consulta institucional
            </span>

        </header>

        <div class="sa-aud-table-wrapper">

            <table class="sa-aud-table">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Responsable</th>
                        <th>Rol</th>
                        <th>Carrera del actor</th>
                        <th>Carrera relacionada</th>
                        <th>Objeto</th>
                        <th>Trámite</th>
                        <th>Estudiante</th>
                        <th>Tabla afectada</th>
                        <th>ID registro</th>
                        <th>Operación</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>Valor anterior</th>
                        <th>Valor nuevo</th>
                        <th>Nivel</th>
                        <th>Dirección IP</th>
                        <th>Fecha y hora</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($itemsAuditoria as $auditoria)

                        @php
                            $fechaRegistro =
                                $auditoria->fecha
                                ?? $auditoria->fecha_accion
                                ?? $auditoria->created_at
                                ?? null;

                            $responsable =
                                $auditoria->usuario_responsable
                                ?? (
                                    isset($auditoria->id_usuario)
                                        ? 'Usuario #'
                                            . $auditoria->id_usuario
                                        : 'No disponible'
                                );

                            $correoResponsable =
                                $auditoria->correo_responsable
                                ?? $auditoria->correo_institucional
                                ?? null;

                            $nombreRol =
                                $auditoria->nombre_rol
                                ?? (
                                    isset($auditoria->id_rol)
                                        ? 'Rol #'
                                            . $auditoria->id_rol
                                        : 'No disponible'
                                );

                            $carreraActor =
                                $auditoria->carrera_actor
                                ?? (
                                    isset($auditoria->id_carrera_actor)
                                        ? 'Carrera #'
                                            . $auditoria->id_carrera_actor
                                        : 'No aplica'
                                );

                            $carreraContexto =
                                $auditoria->carrera_contexto
                                ?? (
                                    isset($auditoria->id_carrera)
                                        ? 'Carrera #'
                                            . $auditoria->id_carrera
                                        : 'No aplica'
                                );

                            $nombreObjeto =
                                $auditoria->nombre_objeto
                                ?? (
                                    isset($auditoria->id_objeto)
                                        ? 'Objeto #'
                                            . $auditoria->id_objeto
                                        : 'No aplica'
                                );

                            $estudiante =
                                $auditoria->estudiante
                                ?? 'No aplica';

                            $correoEstudiante =
                                $auditoria->correo_estudiante
                                ?? null;

                            $operacionCodigo = strtoupper(
                                trim(
                                    (string) (
                                        $auditoria->operacion
                                        ?? 'OTRA'
                                    )
                                )
                            );

                            $operacionTexto =
                                $operacionesDisponibles[
                                    $operacionCodigo
                                ]
                                ?? (
                                    $operacionCodigo === 'LEGADO'
                                        ? 'Registro anterior'
                                        : $operacionCodigo
                                );

                            $operacionClase = match(
                                $operacionCodigo
                            ) {
                                'INSERT' => 'insert',
                                'UPDATE' => 'update',
                                'DELETE' => 'delete',
                                'LOGIN' => 'login',
                                'LOGOUT' => 'logout',
                                'LEGADO' => 'legacy',
                                default => 'other',
                            };

                            $nivel = strtoupper(
                                trim(
                                    (string) (
                                        $auditoria->nivel
                                        ?? 'INFO'
                                    )
                                )
                            );

                            $nivelClase = match($nivel) {
                                'ADVERTENCIA' => 'warning',
                                'ERROR' => 'error',
                                'SEGURIDAD' => 'security',
                                default => 'info',
                            };

                            $tablaAfectada =
                                $auditoria->tabla_afectada
                                ?? 'No identificada';

                            $idRegistro =
                                $auditoria->id_registro
                                ?? null;

                            $accion =
                                $auditoria->accion
                                ?? 'No disponible';

                            $descripcion =
                                $auditoria->descripcion
                                ?? 'Sin descripción disponible.';

                            $valorAnterior =
                                $auditoria->valor_anterior
                                ?? null;

                            $valorNuevo =
                                $auditoria->valor_nuevo
                                ?? null;

                            $direccionIp =
                                $auditoria->ip_address
                                ?? 'No disponible';

                            $userAgent =
                                $auditoria->user_agent
                                ?? 'Información no disponible';
                        @endphp

                        <tr>

                            <td>
                                <span class="sa-aud-record-id">
                                    #{{ $auditoria->id_auditoria ?? 'N/D' }}
                                </span>
                            </td>

                            <td>

                                <div class="sa-aud-person">

                                    <span class="sa-aud-person__avatar">
                                        <i class="fas fa-user"></i>
                                    </span>

                                    <div class="sa-aud-person__information">

                                        <span class="sa-aud-person__name">
                                            {{ $responsable }}
                                        </span>

                                        @if($correoResponsable)

                                            <span class="sa-aud-person__email">
                                                {{ $correoResponsable }}
                                            </span>

                                        @endif

                                    </div>

                                </div>

                            </td>

                            <td>
                                <span class="sa-aud-tag sa-aud-tag--role">
                                    {{ $nombreRol }}
                                </span>
                            </td>

                            <td>
                                <span class="sa-aud-tag sa-aud-tag--career">
                                    {{ $carreraActor }}
                                </span>
                            </td>

                            <td>
                                <span
                                    class="
                                        sa-aud-tag
                                        sa-aud-tag--career-context
                                    "
                                >
                                    {{ $carreraContexto }}
                                </span>
                            </td>

                            <td>
                                <span class="sa-aud-tag sa-aud-tag--object">
                                    {{ $nombreObjeto }}
                                </span>
                            </td>

                            <td>

                                @if($auditoria->id_tramite ?? null)

                                    <span class="sa-aud-record-id">
                                        #{{ $auditoria->id_tramite }}
                                    </span>

                                @else

                                    <span class="sa-aud-muted">
                                        No aplica
                                    </span>

                                @endif

                            </td>

                            <td>

                                @if($estudiante !== 'No aplica')

                                    <div class="sa-aud-student">

                                        <span class="sa-aud-student__name">
                                            {{ $estudiante }}
                                        </span>

                                        @if($correoEstudiante)

                                            <span class="sa-aud-student__email">
                                                {{ $correoEstudiante }}
                                            </span>

                                        @endif

                                    </div>

                                @else

                                    <span class="sa-aud-muted">
                                        No aplica
                                    </span>

                                @endif

                            </td>

                            <td>
                                <code class="sa-aud-code">
                                    {{ $tablaAfectada }}
                                </code>
                            </td>

                            <td>

                                @if($idRegistro !== null)

                                    <span class="sa-aud-record-id">
                                        #{{ $idRegistro }}
                                    </span>

                                @else

                                    <span class="sa-aud-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                            <td>

                                <span
                                    class="
                                        sa-aud-operation
                                        sa-aud-operation--{{ $operacionClase }}
                                    "
                                >
                                    {{ $operacionTexto }}
                                </span>

                            </td>

                            <td>

                                <span
                                    class="
                                        sa-aud-tag
                                        sa-aud-tag--action
                                    "
                                >
                                    {{ $accion }}
                                </span>

                            </td>

                            <td class="sa-aud-description">
                                {{ $descripcion }}
                            </td>

                            <td class="sa-aud-value-cell">

                                @if(
                                    $valorAnterior !== null
                                    && trim(
                                        (string) $valorAnterior
                                    ) !== ''
                                )

                                    <pre class="sa-aud-value-preview">{{ \Illuminate\Support\Str::limit(
                                        (string) $valorAnterior,
                                        300
                                    ) }}</pre>

                                @else

                                    <span class="sa-aud-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                            <td class="sa-aud-value-cell">

                                @if(
                                    $valorNuevo !== null
                                    && trim(
                                        (string) $valorNuevo
                                    ) !== ''
                                )

                                    <pre class="sa-aud-value-preview">{{ \Illuminate\Support\Str::limit(
                                        (string) $valorNuevo,
                                        300
                                    ) }}</pre>

                                @else

                                    <span class="sa-aud-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                            <td>

                                <span
                                    class="
                                        sa-aud-level
                                        sa-aud-level--{{ $nivelClase }}
                                    "
                                >
                                    {{ $nivel }}
                                </span>

                            </td>

                            <td
                                class="sa-aud-nowrap"
                                title="{{ $userAgent }}"
                            >
                                <i class="fas fa-network-wired"></i>

                                {{ $direccionIp }}
                            </td>

                            <td class="sa-aud-nowrap">

                                @if($fechaRegistro)

                                    <i class="far fa-calendar-alt"></i>

                                    {{ \Carbon\Carbon::parse(
                                        $fechaRegistro
                                    )->format('d/m/Y H:i:s') }}

                                @else

                                    <span class="sa-aud-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="18">

                                <div class="sa-aud-empty">

                                    <span class="sa-aud-empty__icon">
                                        <i class="fas fa-folder-open"></i>
                                    </span>

                                    <h3 class="sa-aud-empty__title">
                                        No se encontraron registros
                                    </h3>

                                    <p class="sa-aud-empty__description">
                                        Modifica el rango de fechas o los
                                        filtros seleccionados y realiza
                                        nuevamente la búsqueda.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINACIÓN --}}
        @if(
            $esPaginador
            && $fuenteAuditorias->hasPages()
        )

            <footer class="sa-aud-card__footer">

                {{ $fuenteAuditorias
                    ->appends(request()->query())
                    ->links('pagination::bootstrap-5') }}

            </footer>

        @endif

    </section>

</div>

@endsection