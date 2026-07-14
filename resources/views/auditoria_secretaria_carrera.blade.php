@extends('layouts.app-secretaria')

@section('title', 'Auditoría de Carrera')
@section('titulo', 'Auditoría de Carrera')

@section('content')

@vite([
    'resources/css/auditoria_secretaria_carrera.css',
    'resources/js/auditoria_secretaria_carrera.js'
])

@php
    /*
    |--------------------------------------------------------------------------
    | NORMALIZACIÓN DE VARIABLES
    |--------------------------------------------------------------------------
    */

    $fuenteAuditorias = $auditorias ?? $registros ?? collect();

    $esPaginador = $fuenteAuditorias instanceof
        \Illuminate\Pagination\AbstractPaginator;

    $esPaginadorCompleto = $fuenteAuditorias instanceof
        \Illuminate\Pagination\LengthAwarePaginator;

    $itemsAuditoria = $esPaginador
        ? collect($fuenteAuditorias->items())
        : (
            $fuenteAuditorias instanceof \Illuminate\Support\Collection
                ? $fuenteAuditorias
                : collect($fuenteAuditorias)
        );

    $totalRegistros = $esPaginadorCompleto
        ? $fuenteAuditorias->total()
        : $itemsAuditoria->count();

    $rolesDisponibles = $roles ?? collect();
    $objetosDisponibles = $objetos ?? collect();
    $carrerasDisponibles = $carreras ?? collect();

    $operacionesDisponibles = $operaciones ?? [
        'INSERT' => 'Creación',
        'UPDATE' => 'Actualización',
        'DELETE' => 'Eliminación',
        'LOGIN' => 'Inicio de sesión',
        'LOGOUT' => 'Cierre de sesión',
        'OTRA' => 'Otra operación',
    ];

    $nivelesDisponibles = $niveles ?? [
        'INFO' => 'Información',
        'ADVERTENCIA' => 'Advertencia',
        'ERROR' => 'Error',
        'SEGURIDAD' => 'Seguridad',
    ];

    $carreraAsignada = optional(
        $carrerasDisponibles->first()
    )->nombre_carrera ?? 'Carrera asignada';
@endphp

<div class="sc-aud-page">

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}
    <section class="sc-aud-hero">

        <div class="sc-aud-hero__content">

            <div class="sc-aud-hero__information">

                <span class="sc-aud-hero__eyebrow">
                    Secretaría de Carrera
                </span>

                <h1 class="sc-aud-hero__title">
                    Auditoría de Carrera
                </h1>

                <p class="sc-aud-hero__description">
                    Consulta los cambios y operaciones relevantes
                    realizados dentro de la carrera asignada.
                </p>

            </div>

            <div class="sc-aud-hero__actions">

                <span class="sc-aud-career-badge">
                    <i class="fas fa-graduation-cap"></i>

                    {{ $carreraAsignada }}
                </span>

                <a
                    href="{{ url()->previous() }}"
                    class="sc-aud-button sc-aud-button--back"
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
            class="sc-aud-alert sc-aud-alert--success"
            data-sc-aud-auto-close
        >
            <i class="fas fa-check-circle"></i>

            <span>
                {{ session('success') }}
            </span>
        </div>

    @endif

    @if(session('error'))

        <div class="sc-aud-alert sc-aud-alert--danger">

            <i class="fas fa-exclamation-triangle"></i>

            <span>
                {{ session('error') }}
            </span>

        </div>

    @endif

    @if($errors->any())

        <div class="sc-aud-alert sc-aud-alert--danger">

            <i class="fas fa-circle-exclamation"></i>

            <div>

                <strong>
                    No se pudo realizar la búsqueda.
                </strong>

                <ul class="sc-aud-alert__list">

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
    <section class="sc-aud-summary">

        <article class="sc-aud-summary-card">

            <span class="sc-aud-summary-card__icon">
                <i class="fas fa-clipboard-check"></i>
            </span>

            <div>

                <span class="sc-aud-summary-card__label">
                    Registros encontrados
                </span>

                <strong class="sc-aud-summary-card__value">
                    {{ $totalRegistros }}
                </strong>

            </div>

        </article>

        <article class="sc-aud-summary-card">

            <span class="sc-aud-summary-card__icon">
                <i class="fas fa-building-columns"></i>
            </span>

            <div>

                <span class="sc-aud-summary-card__label">
                    Alcance de consulta
                </span>

                <strong
                    class="
                        sc-aud-summary-card__value
                        sc-aud-summary-card__value--text
                    "
                >
                    Carrera asignada
                </strong>

            </div>

        </article>

        <article class="sc-aud-summary-card">

            <span class="sc-aud-summary-card__icon">
                <i class="fas fa-calendar-days"></i>
            </span>

            <div>

                <span class="sc-aud-summary-card__label">
                    Período consultado
                </span>

                <strong
                    class="
                        sc-aud-summary-card__value
                        sc-aud-summary-card__value--date
                    "
                >
                    {{ \Carbon\Carbon::parse(
                        $filtros['fecha_inicio']
                        ?? now()->subMonth()->toDateString()
                    )->format('d/m/Y') }}

                    —

                    {{ \Carbon\Carbon::parse(
                        $filtros['fecha_fin']
                        ?? now()->toDateString()
                    )->format('d/m/Y') }}
                </strong>

            </div>

        </article>

    </section>

    {{-- =====================================================
         FILTROS
    ====================================================== --}}
    <section class="sc-aud-card">

        <header class="sc-aud-card__header">

            <div class="sc-aud-card__heading">

                <span class="sc-aud-card__icon">
                    <i class="fas fa-filter"></i>
                </span>

                <div>

                    <h2 class="sc-aud-card__title">
                        Filtros de búsqueda
                    </h2>

                    <p class="sc-aud-card__subtitle">
                        Utiliza uno o varios criterios para localizar
                        cambios específicos dentro de la carrera.
                    </p>

                </div>

            </div>

        </header>

        <div class="sc-aud-card__body">

            <form
                id="formAuditoriaSecretariaCarrera"
                method="GET"
                action="{{ url()->current() }}"
                autocomplete="off"
            >

                <div class="sc-aud-filter-grid">

                    {{-- FECHA INICIAL --}}
                    <div class="sc-aud-field">

                        <label
                            for="fecha_inicio"
                            class="sc-aud-label"
                        >
                            Fecha inicial
                        </label>

                        <input
                            type="date"
                            id="fecha_inicio"
                            name="fecha_inicio"
                            class="sc-aud-input"
                            value="{{ request(
                                'fecha_inicio',
                                $filtros['fecha_inicio'] ?? ''
                            ) }}"
                            required
                        >

                        <span
                            id="errorFechaInicioAuditoriaSC"
                            class="sc-aud-error"
                        >
                            Debe seleccionar la fecha inicial.
                        </span>

                    </div>

                    {{-- FECHA FINAL --}}
                    <div class="sc-aud-field">

                        <label
                            for="fecha_fin"
                            class="sc-aud-label"
                        >
                            Fecha final
                        </label>

                        <input
                            type="date"
                            id="fecha_fin"
                            name="fecha_fin"
                            class="sc-aud-input"
                            value="{{ request(
                                'fecha_fin',
                                $filtros['fecha_fin'] ?? ''
                            ) }}"
                            required
                        >

                        <span
                            id="errorFechaFinAuditoriaSC"
                            class="sc-aud-error"
                        >
                            La fecha final no puede ser anterior
                            a la fecha inicial.
                        </span>

                    </div>

                    {{-- USUARIO --}}
                    <div class="sc-aud-field">

                        <label
                            for="id_usuario"
                            class="sc-aud-label"
                        >
                            ID de usuario
                        </label>

                        <input
                            type="number"
                            id="id_usuario"
                            name="id_usuario"
                            class="sc-aud-input"
                            value="{{ request('id_usuario') }}"
                            min="1"
                            step="1"
                            placeholder="Ej. 87"
                            data-sc-aud-optional
                        >

                        <span
                            id="errorUsuarioAuditoriaSC"
                            class="sc-aud-error"
                        >
                            Ingrese un ID de usuario válido.
                        </span>

                    </div>

                    {{-- ROL --}}
                    <div class="sc-aud-field">

                        <label
                            for="id_rol"
                            class="sc-aud-label"
                        >
                            Rol
                        </label>

                        <select
                            id="id_rol"
                            name="id_rol"
                            class="sc-aud-select"
                            data-sc-aud-optional
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
                    <div class="sc-aud-field">

                        <label
                            for="id_objeto"
                            class="sc-aud-label"
                        >
                            Objeto
                        </label>

                        <select
                            id="id_objeto"
                            name="id_objeto"
                            class="sc-aud-select"
                            data-sc-aud-optional
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
                                        ?? 'Objeto #' . $objeto->id_objeto }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- TRÁMITE --}}
                    <div class="sc-aud-field">

                        <label
                            for="id_tramite"
                            class="sc-aud-label"
                        >
                            Número de trámite
                        </label>

                        <input
                            type="number"
                            id="id_tramite"
                            name="id_tramite"
                            class="sc-aud-input"
                            value="{{ request('id_tramite') }}"
                            min="1"
                            step="1"
                            placeholder="Ej. 146"
                            data-sc-aud-optional
                        >

                        <span
                            id="errorTramiteAuditoriaSC"
                            class="sc-aud-error"
                        >
                            Ingrese un número de trámite válido.
                        </span>

                    </div>

                    {{-- OPERACIÓN --}}
                    <div class="sc-aud-field">

                        <label
                            for="operacion"
                            class="sc-aud-label"
                        >
                            Operación
                        </label>

                        <select
                            id="operacion"
                            name="operacion"
                            class="sc-aud-select"
                            data-sc-aud-optional
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
                    <div class="sc-aud-field">

                        <label
                            for="tabla_afectada"
                            class="sc-aud-label"
                        >
                            Tabla afectada
                        </label>

                        <input
                            type="text"
                            id="tabla_afectada"
                            name="tabla_afectada"
                            class="sc-aud-input"
                            value="{{ request('tabla_afectada') }}"
                            maxlength="100"
                            placeholder="Ej. tbl_tramite"
                            data-sc-aud-optional
                        >

                    </div>

                    {{-- MÓDULO --}}
                    <div class="sc-aud-field">

                        <label
                            for="modulo"
                            class="sc-aud-label"
                        >
                            Módulo
                        </label>

                        <input
                            type="text"
                            id="modulo"
                            name="modulo"
                            class="sc-aud-input"
                            value="{{ request('modulo') }}"
                            maxlength="100"
                            placeholder="Ej. Cambio de carrera"
                            data-sc-aud-optional
                        >

                    </div>

                    {{-- ACCIÓN --}}
                    <div class="sc-aud-field">

                        <label
                            for="accion"
                            class="sc-aud-label"
                        >
                            Acción
                        </label>

                        <input
                            type="text"
                            id="accion"
                            name="accion"
                            class="sc-aud-input"
                            value="{{ request('accion') }}"
                            maxlength="100"
                            placeholder="Ej. ACTUALIZAR_ESTADO"
                            data-sc-aud-optional
                        >

                    </div>

                    {{-- NIVEL --}}
                    <div class="sc-aud-field">

                        <label
                            for="nivel"
                            class="sc-aud-label"
                        >
                            Nivel
                        </label>

                        <select
                            id="nivel"
                            name="nivel"
                            class="sc-aud-select"
                            data-sc-aud-optional
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
                    <div class="sc-aud-field">

                        <label
                            for="per_page"
                            class="sc-aud-label"
                        >
                            Registros
                        </label>

                        <select
                            id="per_page"
                            name="per_page"
                            class="sc-aud-select"
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

                <div class="sc-aud-filter-actions">

                    <button
                        type="submit"
                        id="btnBuscarAuditoriaSC"
                        class="
                            sc-aud-button
                            sc-aud-button--search
                        "
                    >
                        <i class="fas fa-search"></i>
                        <span>Buscar</span>
                    </button>

                    <a
                        href="{{ url()->current() }}"
                        class="
                            sc-aud-button
                            sc-aud-button--clear
                        "
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
    <section class="sc-aud-card">

        <header
            class="
                sc-aud-card__header
                sc-aud-card__header--between
            "
        >

            <div class="sc-aud-card__heading">

                <span class="sc-aud-card__icon">
                    <i class="fas fa-clipboard-list"></i>
                </span>

                <div>

                    <h2 class="sc-aud-card__title">
                        Registros de auditoría
                    </h2>

                    <p class="sc-aud-card__subtitle">
                        Se encontraron

                        <strong>
                            {{ $totalRegistros }}
                        </strong>

                        registros relacionados con la carrera.
                    </p>

                </div>

            </div>

            <span class="sc-aud-context">
                <i class="fas fa-lock"></i>
                Consulta restringida por carrera
            </span>

        </header>

        <div class="sc-aud-table-wrapper">

            <table class="sc-aud-table">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Responsable</th>
                        <th>Rol</th>
                        <th>Carrera</th>
                        <th>Objeto</th>
                        <th>Trámite</th>
                        <th>Estudiante</th>
                        <th>Operación</th>
                        <th>Tabla</th>
                        <th>Módulo</th>
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
                                        ? 'Rol #' . $auditoria->id_rol
                                        : 'No disponible'
                                );

                            $carreraRegistro =
                                $auditoria->carrera_contexto
                                ?? $auditoria->carrera_actor
                                ?? $carreraAsignada;

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
                                ?? $operacionCodigo;

                            $operacionClase = match(
                                $operacionCodigo
                            ) {
                                'INSERT' => 'insert',
                                'UPDATE' => 'update',
                                'DELETE' => 'delete',
                                'LOGIN' => 'login',
                                'LOGOUT' => 'logout',
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
                                ?? 'No disponible';

                            $modulo =
                                $auditoria->modulo
                                ?? 'General';

                            $accion =
                                $auditoria->accion
                                ?? 'No disponible';

                            $descripcion =
                                $auditoria->descripcion
                                ?? $auditoria->detalle
                                ?? 'Sin descripción disponible.';

                            $valorAnterior =
                                $auditoria->valor_anterior
                                ?? $auditoria->datos_anteriores
                                ?? $auditoria->old_values
                                ?? null;

                            $valorNuevo =
                                $auditoria->valor_nuevo
                                ?? $auditoria->datos_nuevos
                                ?? $auditoria->new_values
                                ?? null;

                            $direccionIp =
                                $auditoria->ip_address
                                ?? $auditoria->ip
                                ?? 'No disponible';

                            $userAgent =
                                $auditoria->user_agent
                                ?? 'Información no disponible';
                        @endphp

                        <tr>

                            {{-- ID --}}
                            <td>

                                <span class="sc-aud-record-id">
                                    #{{ $auditoria->id_auditoria
                                        ?? 'N/D' }}
                                </span>

                            </td>

                            {{-- RESPONSABLE --}}
                            <td>

                                <div class="sc-aud-person">

                                    <span class="sc-aud-person__avatar">
                                        <i class="fas fa-user"></i>
                                    </span>

                                    <div class="sc-aud-person__information">

                                        <span class="sc-aud-person__name">
                                            {{ $responsable }}
                                        </span>

                                        @if($correoResponsable)

                                            <span
                                                class="
                                                    sc-aud-person__email
                                                "
                                            >
                                                {{ $correoResponsable }}
                                            </span>

                                        @endif

                                    </div>

                                </div>

                            </td>

                            {{-- ROL --}}
                            <td>

                                <span
                                    class="
                                        sc-aud-tag
                                        sc-aud-tag--role
                                    "
                                >
                                    {{ $nombreRol }}
                                </span>

                            </td>

                            {{-- CARRERA --}}
                            <td>

                                <span
                                    class="
                                        sc-aud-tag
                                        sc-aud-tag--career
                                    "
                                >
                                    {{ $carreraRegistro }}
                                </span>

                            </td>

                            {{-- OBJETO --}}
                            <td>

                                <span
                                    class="
                                        sc-aud-tag
                                        sc-aud-tag--object
                                    "
                                >
                                    {{ $nombreObjeto }}
                                </span>

                            </td>

                            {{-- TRÁMITE --}}
                            <td>

                                @if($auditoria->id_tramite ?? null)

                                    <span class="sc-aud-record-id">
                                        #{{ $auditoria->id_tramite }}
                                    </span>

                                @else

                                    <span class="sc-aud-muted">
                                        No aplica
                                    </span>

                                @endif

                            </td>

                            {{-- ESTUDIANTE --}}
                            <td>

                                @if($estudiante !== 'No aplica')

                                    <div class="sc-aud-student">

                                        <span
                                            class="
                                                sc-aud-student__name
                                            "
                                        >
                                            {{ $estudiante }}
                                        </span>

                                        @if($correoEstudiante)

                                            <span
                                                class="
                                                    sc-aud-student__email
                                                "
                                            >
                                                {{ $correoEstudiante }}
                                            </span>

                                        @endif

                                    </div>

                                @else

                                    <span class="sc-aud-muted">
                                        No aplica
                                    </span>

                                @endif

                            </td>

                            {{-- OPERACIÓN --}}
                            <td>

                                <span
                                    class="
                                        sc-aud-operation
                                        sc-aud-operation--{{ $operacionClase }}
                                    "
                                >
                                    {{ $operacionTexto }}
                                </span>

                            </td>

                            {{-- TABLA --}}
                            <td>

                                <code class="sc-aud-code">
                                    {{ $tablaAfectada }}
                                </code>

                            </td>

                            {{-- MÓDULO --}}
                            <td>

                                <span
                                    class="
                                        sc-aud-tag
                                        sc-aud-tag--module
                                    "
                                >
                                    {{ $modulo }}
                                </span>

                            </td>

                            {{-- ACCIÓN --}}
                            <td>

                                <span
                                    class="
                                        sc-aud-tag
                                        sc-aud-tag--action
                                    "
                                >
                                    {{ $accion }}
                                </span>

                            </td>

                            {{-- DESCRIPCIÓN --}}
                            <td class="sc-aud-description">
                                {{ $descripcion }}
                            </td>

                            {{-- VALOR ANTERIOR --}}
                            <td class="sc-aud-value-cell">

                                @if(
                                    $valorAnterior !== null
                                    && trim((string) $valorAnterior) !== ''
                                )

                                    <pre class="sc-aud-value-preview">{{ \Illuminate\Support\Str::limit(
                                        (string) $valorAnterior,
                                        250
                                    ) }}</pre>

                                @else

                                    <span class="sc-aud-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                            {{-- VALOR NUEVO --}}
                            <td class="sc-aud-value-cell">

                                @if(
                                    $valorNuevo !== null
                                    && trim((string) $valorNuevo) !== ''
                                )

                                    <pre class="sc-aud-value-preview">{{ \Illuminate\Support\Str::limit(
                                        (string) $valorNuevo,
                                        250
                                    ) }}</pre>

                                @else

                                    <span class="sc-aud-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                            {{-- NIVEL --}}
                            <td>

                                <span
                                    class="
                                        sc-aud-level
                                        sc-aud-level--{{ $nivelClase }}
                                    "
                                >
                                    {{ $nivel }}
                                </span>

                            </td>

                            {{-- IP --}}
                            <td
                                class="sc-aud-nowrap"
                                title="{{ $userAgent }}"
                            >
                                <i class="fas fa-network-wired"></i>

                                {{ $direccionIp }}
                            </td>

                            {{-- FECHA --}}
                            <td class="sc-aud-nowrap">

                                @if($fechaRegistro)

                                    <i class="far fa-calendar-alt"></i>

                                    {{ \Carbon\Carbon::parse(
                                        $fechaRegistro
                                    )->format('d/m/Y H:i:s') }}

                                @else

                                    <span class="sc-aud-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="17">

                                <div class="sc-aud-empty">

                                    <span class="sc-aud-empty__icon">
                                        <i class="fas fa-folder-open"></i>
                                    </span>

                                    <h3 class="sc-aud-empty__title">
                                        No se encontraron registros
                                    </h3>

                                    <p class="sc-aud-empty__description">
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

            <footer class="sc-aud-card__footer">

                {{ $fuenteAuditorias
                    ->appends(request()->query())
                    ->links('pagination::bootstrap-5') }}

            </footer>

        @endif

    </section>

</div>

@endsection