@extends('layouts.app-coordinador')

@section('title', 'Auditoría de Carrera')
@section('titulo', 'Auditoría de Carrera')

@section('content')

@vite([
    'resources/css/auditoria_coordinador.css',
    'resources/js/auditoria_coordinador.js'
])

@php
    /*
    |--------------------------------------------------------------------------
    | NORMALIZACIÓN DE RESULTADOS
    |--------------------------------------------------------------------------
    */

    $fuenteAuditorias = $auditorias ?? $registros ?? collect();

    $esPaginadorCompleto =
        $fuenteAuditorias instanceof
        \Illuminate\Pagination\LengthAwarePaginator;

    $esPaginadorSimple =
        $fuenteAuditorias instanceof
        \Illuminate\Pagination\Paginator;

    $esPaginador =
        $esPaginadorCompleto || $esPaginadorSimple;

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
        'LEGADO' => 'Registro anterior',
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

<div class="coord-aud-page">

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}
    <section class="coord-aud-hero">

        <div class="coord-aud-hero__content">

            <div class="coord-aud-hero__information">

                <span class="coord-aud-hero__eyebrow">
                    Coordinación de Carrera
                </span>

                <h1 class="coord-aud-hero__title">
                    Auditoría de Carrera
                </h1>

                <p class="coord-aud-hero__description">
                    Consulta las operaciones y cambios importantes
                    registrados dentro de la carrera asignada.
                </p>

            </div>

            <div class="coord-aud-hero__actions">

                <span class="coord-aud-career-badge">
                    <i class="fas fa-graduation-cap"></i>

                    {{ $carreraAsignada }}
                </span>

                <a
                    href="{{ url()->previous() }}"
                    class="
                        coord-aud-button
                        coord-aud-button--back
                    "
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
            class="
                coord-aud-alert
                coord-aud-alert--success
            "
            data-coord-aud-auto-close
        >
            <i class="fas fa-check-circle"></i>

            <span>
                {{ session('success') }}
            </span>
        </div>

    @endif

    @if(session('error'))

        <div
            class="
                coord-aud-alert
                coord-aud-alert--danger
            "
        >
            <i class="fas fa-exclamation-triangle"></i>

            <span>
                {{ session('error') }}
            </span>
        </div>

    @endif

    @if($errors->any())

        <div
            class="
                coord-aud-alert
                coord-aud-alert--danger
            "
        >
            <i class="fas fa-exclamation-circle"></i>

            <div>

                <strong>
                    No se pudo realizar la búsqueda.
                </strong>

                <ul class="coord-aud-alert__list">

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
    <section class="coord-aud-summary">

        <article class="coord-aud-summary-card">

            <span class="coord-aud-summary-card__icon">
                <i class="fas fa-clipboard-check"></i>
            </span>

            <div>

                <span class="coord-aud-summary-card__label">
                    Registros encontrados
                </span>

                <strong class="coord-aud-summary-card__value">
                    {{ $totalRegistros }}
                </strong>

            </div>

        </article>

        <article class="coord-aud-summary-card">

            <span class="coord-aud-summary-card__icon">
                <i class="fas fa-building-columns"></i>
            </span>

            <div>

                <span class="coord-aud-summary-card__label">
                    Alcance de consulta
                </span>

                <strong
                    class="
                        coord-aud-summary-card__value
                        coord-aud-summary-card__value--text
                    "
                >
                    Carrera asignada
                </strong>

            </div>

        </article>

        <article class="coord-aud-summary-card">

            <span class="coord-aud-summary-card__icon">
                <i class="fas fa-calendar-days"></i>
            </span>

            <div>

                <span class="coord-aud-summary-card__label">
                    Período consultado
                </span>

                <strong
                    class="
                        coord-aud-summary-card__value
                        coord-aud-summary-card__value--date
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
    <section class="coord-aud-card">

        <header class="coord-aud-card__header">

            <div class="coord-aud-card__heading">

                <span class="coord-aud-card__icon">
                    <i class="fas fa-filter"></i>
                </span>

                <div>

                    <h2 class="coord-aud-card__title">
                        Filtros de búsqueda
                    </h2>

                    <p class="coord-aud-card__subtitle">
                        Utiliza uno o varios criterios para localizar
                        operaciones específicas de la carrera.
                    </p>

                </div>

            </div>

        </header>

        <div class="coord-aud-card__body">

            <form
                id="formAuditoriaCoordinador"
                method="GET"
                action="{{ route('auditoria.coordinador') }}"
                autocomplete="off"
            >

                <div class="coord-aud-filter-grid">

                    {{-- FECHA INICIAL --}}
                    <div class="coord-aud-field">

                        <label
                            for="fecha_inicio"
                            class="coord-aud-label"
                        >
                            Fecha inicial
                        </label>

                        <input
                            type="date"
                            id="fecha_inicio"
                            name="fecha_inicio"
                            class="coord-aud-input"
                            value="{{ $fechaInicioActual }}"
                            required
                        >

                        <span
                            id="errorFechaInicioAuditoriaCoordinador"
                            class="coord-aud-error"
                        >
                            Debe seleccionar la fecha inicial.
                        </span>

                    </div>

                    {{-- FECHA FINAL --}}
                    <div class="coord-aud-field">

                        <label
                            for="fecha_fin"
                            class="coord-aud-label"
                        >
                            Fecha final
                        </label>

                        <input
                            type="date"
                            id="fecha_fin"
                            name="fecha_fin"
                            class="coord-aud-input"
                            value="{{ $fechaFinActual }}"
                            required
                        >

                        <span
                            id="errorFechaFinAuditoriaCoordinador"
                            class="coord-aud-error"
                        >
                            La fecha final no puede ser anterior
                            a la fecha inicial.
                        </span>

                    </div>

                    {{-- ID DE USUARIO --}}
                    <div class="coord-aud-field">

                        <label
                            for="id_usuario"
                            class="coord-aud-label"
                        >
                            ID de usuario
                        </label>

                        <input
                            type="number"
                            id="id_usuario"
                            name="id_usuario"
                            class="coord-aud-input"
                            value="{{ request('id_usuario') }}"
                            min="1"
                            step="1"
                            placeholder="Ej. 144"
                            data-coord-aud-optional
                        >

                        <span
                            id="errorUsuarioAuditoriaCoordinador"
                            class="coord-aud-error"
                        >
                            Ingrese un ID de usuario válido.
                        </span>

                    </div>

                    {{-- ROL --}}
                    <div class="coord-aud-field">

                        <label
                            for="id_rol"
                            class="coord-aud-label"
                        >
                            Rol
                        </label>

                        <select
                            id="id_rol"
                            name="id_rol"
                            class="coord-aud-select"
                            data-coord-aud-optional
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
                    <div class="coord-aud-field">

                        <label
                            for="id_objeto"
                            class="coord-aud-label"
                        >
                            Objeto
                        </label>

                        <select
                            id="id_objeto"
                            name="id_objeto"
                            class="coord-aud-select"
                            data-coord-aud-optional
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
                    <div class="coord-aud-field">

                        <label
                            for="id_tramite"
                            class="coord-aud-label"
                        >
                            Número de trámite
                        </label>

                        <input
                            type="number"
                            id="id_tramite"
                            name="id_tramite"
                            class="coord-aud-input"
                            value="{{ request('id_tramite') }}"
                            min="1"
                            step="1"
                            placeholder="Ej. 146"
                            data-coord-aud-optional
                        >

                        <span
                            id="errorTramiteAuditoriaCoordinador"
                            class="coord-aud-error"
                        >
                            Ingrese un número de trámite válido.
                        </span>

                    </div>

                    {{-- OPERACIÓN --}}
                    <div class="coord-aud-field">

                        <label
                            for="operacion"
                            class="coord-aud-label"
                        >
                            Operación
                        </label>

                        <select
                            id="operacion"
                            name="operacion"
                            class="coord-aud-select"
                            data-coord-aud-optional
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
                    <div class="coord-aud-field">

                        <label
                            for="tabla_afectada"
                            class="coord-aud-label"
                        >
                            Tabla afectada
                        </label>

                        <input
                            type="text"
                            id="tabla_afectada"
                            name="tabla_afectada"
                            class="coord-aud-input"
                            value="{{ request('tabla_afectada') }}"
                            maxlength="100"
                            placeholder="Ej. tbl_tramite"
                            data-coord-aud-optional
                        >

                    </div>

                    {{-- ACCIÓN --}}
                    <div class="coord-aud-field">

                        <label
                            for="accion"
                            class="coord-aud-label"
                        >
                            Acción
                        </label>

                        <input
                            type="text"
                            id="accion"
                            name="accion"
                            class="coord-aud-input"
                            value="{{ request('accion') }}"
                            maxlength="100"
                            placeholder="Ej. ACTUALIZAR_ESTADO"
                            data-coord-aud-optional
                        >

                    </div>

                    {{-- NIVEL --}}
                    <div class="coord-aud-field">

                        <label
                            for="nivel"
                            class="coord-aud-label"
                        >
                            Nivel
                        </label>

                        <select
                            id="nivel"
                            name="nivel"
                            class="coord-aud-select"
                            data-coord-aud-optional
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

                    {{-- CANTIDAD --}}
                    <div class="coord-aud-field">

                        <label
                            for="per_page"
                            class="coord-aud-label"
                        >
                            Registros
                        </label>

                        <select
                            id="per_page"
                            name="per_page"
                            class="coord-aud-select"
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

                <div class="coord-aud-filter-actions">

                    <button
                        type="submit"
                        id="btnBuscarAuditoriaCoordinador"
                        class="
                            coord-aud-button
                            coord-aud-button--search
                        "
                    >
                        <i class="fas fa-search"></i>
                        <span>Buscar</span>
                    </button>

                    <a
                        href="{{ route('auditoria.coordinador') }}"
                        class="
                            coord-aud-button
                            coord-aud-button--clear
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
    <section class="coord-aud-card">

        <header
            class="
                coord-aud-card__header
                coord-aud-card__header--between
            "
        >

            <div class="coord-aud-card__heading">

                <span class="coord-aud-card__icon">
                    <i class="fas fa-clipboard-list"></i>
                </span>

                <div>

                    <h2 class="coord-aud-card__title">
                        Registros de auditoría
                    </h2>

                    <p class="coord-aud-card__subtitle">
                        Se encontraron

                        <strong>
                            {{ $totalRegistros }}
                        </strong>

                        registros relacionados con la carrera.
                    </p>

                </div>

            </div>

            <span class="coord-aud-context">
                <i class="fas fa-lock"></i>
                Consulta restringida por carrera
            </span>

        </header>

        <div class="coord-aud-table-wrapper">

            <table class="coord-aud-table">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Responsable</th>
                        <th>Rol</th>
                        <th>Carrera</th>
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

                            {{-- ID --}}
                            <td>

                                <span class="coord-aud-record-id">
                                    #{{ $auditoria->id_auditoria
                                        ?? 'N/D' }}
                                </span>

                            </td>

                            {{-- RESPONSABLE --}}
                            <td>

                                <div class="coord-aud-person">

                                    <span class="coord-aud-person__avatar">
                                        <i class="fas fa-user"></i>
                                    </span>

                                    <div
                                        class="
                                            coord-aud-person__information
                                        "
                                    >

                                        <span class="coord-aud-person__name">
                                            {{ $responsable }}
                                        </span>

                                        @if($correoResponsable)

                                            <span
                                                class="
                                                    coord-aud-person__email
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
                                        coord-aud-tag
                                        coord-aud-tag--role
                                    "
                                >
                                    {{ $nombreRol }}
                                </span>

                            </td>

                            {{-- CARRERA --}}
                            <td>

                                <span
                                    class="
                                        coord-aud-tag
                                        coord-aud-tag--career
                                    "
                                >
                                    {{ $carreraRegistro }}
                                </span>

                            </td>

                            {{-- OBJETO --}}
                            <td>

                                <span
                                    class="
                                        coord-aud-tag
                                        coord-aud-tag--object
                                    "
                                >
                                    {{ $nombreObjeto }}
                                </span>

                            </td>

                            {{-- TRÁMITE --}}
                            <td>

                                @if($auditoria->id_tramite ?? null)

                                    <span class="coord-aud-record-id">
                                        #{{ $auditoria->id_tramite }}
                                    </span>

                                @else

                                    <span class="coord-aud-muted">
                                        No aplica
                                    </span>

                                @endif

                            </td>

                            {{-- ESTUDIANTE --}}
                            <td>

                                @if($estudiante !== 'No aplica')

                                    <div class="coord-aud-student">

                                        <span
                                            class="
                                                coord-aud-student__name
                                            "
                                        >
                                            {{ $estudiante }}
                                        </span>

                                        @if($correoEstudiante)

                                            <span
                                                class="
                                                    coord-aud-student__email
                                                "
                                            >
                                                {{ $correoEstudiante }}
                                            </span>

                                        @endif

                                    </div>

                                @else

                                    <span class="coord-aud-muted">
                                        No aplica
                                    </span>

                                @endif

                            </td>

                            {{-- TABLA --}}
                            <td>

                                <code class="coord-aud-code">
                                    {{ $tablaAfectada }}
                                </code>

                            </td>

                            {{-- ID DEL REGISTRO --}}
                            <td>

                                @if($idRegistro !== null)

                                    <span class="coord-aud-record-id">
                                        #{{ $idRegistro }}
                                    </span>

                                @else

                                    <span class="coord-aud-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                            {{-- OPERACIÓN --}}
                            <td>

                                <span
                                    class="
                                        coord-aud-operation
                                        coord-aud-operation--{{ $operacionClase }}
                                    "
                                >
                                    {{ $operacionTexto }}
                                </span>

                            </td>

                            {{-- ACCIÓN --}}
                            <td>

                                <span
                                    class="
                                        coord-aud-tag
                                        coord-aud-tag--action
                                    "
                                >
                                    {{ $accion }}
                                </span>

                            </td>

                            {{-- DESCRIPCIÓN --}}
                            <td class="coord-aud-description">

                                {{ $descripcion }}

                            </td>

                            {{-- VALOR ANTERIOR --}}
                            <td class="coord-aud-value-cell">

                                @if(
                                    $valorAnterior !== null
                                    && trim(
                                        (string) $valorAnterior
                                    ) !== ''
                                )

                                    <pre class="coord-aud-value-preview">{{ \Illuminate\Support\Str::limit(
                                        (string) $valorAnterior,
                                        300
                                    ) }}</pre>

                                @else

                                    <span class="coord-aud-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                            {{-- VALOR NUEVO --}}
                            <td class="coord-aud-value-cell">

                                @if(
                                    $valorNuevo !== null
                                    && trim(
                                        (string) $valorNuevo
                                    ) !== ''
                                )

                                    <pre class="coord-aud-value-preview">{{ \Illuminate\Support\Str::limit(
                                        (string) $valorNuevo,
                                        300
                                    ) }}</pre>

                                @else

                                    <span class="coord-aud-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                            {{-- NIVEL --}}
                            <td>

                                <span
                                    class="
                                        coord-aud-level
                                        coord-aud-level--{{ $nivelClase }}
                                    "
                                >
                                    {{ $nivel }}
                                </span>

                            </td>

                            {{-- DIRECCIÓN IP --}}
                            <td
                                class="coord-aud-nowrap"
                                title="{{ $userAgent }}"
                            >

                                <i class="fas fa-network-wired"></i>

                                {{ $direccionIp }}

                            </td>

                            {{-- FECHA --}}
                            <td class="coord-aud-nowrap">

                                @if($fechaRegistro)

                                    <i class="far fa-calendar-alt"></i>

                                    {{ \Carbon\Carbon::parse(
                                        $fechaRegistro
                                    )->format('d/m/Y H:i:s') }}

                                @else

                                    <span class="coord-aud-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="17">

                                <div class="coord-aud-empty">

                                    <span class="coord-aud-empty__icon">
                                        <i class="fas fa-folder-open"></i>
                                    </span>

                                    <h3 class="coord-aud-empty__title">
                                        No se encontraron registros
                                    </h3>

                                    <p class="coord-aud-empty__description">
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

        {{-- =================================================
             PAGINACIÓN
        ================================================== --}}
        @if(
            $esPaginador
            && $fuenteAuditorias->hasPages()
        )

            <footer class="coord-aud-card__footer">

                {{ $fuenteAuditorias
                    ->appends(request()->query())
                    ->links('pagination::bootstrap-5') }}

            </footer>

        @endif

    </section>

</div>

@endsection