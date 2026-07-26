@extends('layouts.app-secretaria')

@section('title', 'Bitácora Global')
@section('titulo', 'Bitácora Global')

@section('content')

@vite([
    'resources/css/bitacora_secretaria_general.css',
    'resources/js/bitacora_secretaria_general.js'
])

@php
    /*
    |--------------------------------------------------------------------------
    | NORMALIZACIÓN DE RESULTADOS
    |--------------------------------------------------------------------------
    |
    | El servicio actualmente puede devolver una Collection.
    | Por eso no se utilizan total(), links(), appends() ni hasPages().
    |
    */

    $registros = $bitacoras ?? collect();

    if (
        $registros instanceof
        \Illuminate\Contracts\Pagination\Paginator
    ) {
        $registros = collect($registros->items());
    } elseif (
        !($registros instanceof \Illuminate\Support\Collection)
    ) {
        $registros = collect($registros);
    }

    $totalRegistros = $registros->count();

    $rolesDisponibles = [
        1 => 'Secretaría General',
        2 => 'Estudiante',
        4 => 'Coordinador de Carrera',
        5 => 'Secretaría de Carrera',
    ];
@endphp

<div class="sg-bita-page">

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}
    <section class="sg-bita-hero">

        <div class="sg-bita-hero__content">

            <div class="sg-bita-hero__information">

                <span class="sg-bita-hero__eyebrow">
                    Secretaría Académica
                </span>

                <h1 class="sg-bita-hero__title">
                    Bitácora Global del Sistema
                </h1>

                <p class="sg-bita-hero__description">
                    Consulta las acciones registradas por los usuarios
                    en todas las carreras, módulos y trámites del sistema.
                </p>

            </div>

            <div class="sg-bita-hero__actions">

                <span class="sg-bita-global-badge">
                    <i class="fas fa-globe-americas"></i>
                    Acceso global
                </span>

                <a
                    href="{{ url()->previous() }}"
                    class="sg-bita-button sg-bita-button--back"
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
            class="sg-bita-alert sg-bita-alert--success"
            data-sg-bita-auto-close
        >
            <i class="fas fa-check-circle"></i>

            <span>
                {{ session('success') }}
            </span>
        </div>

    @endif

    @if(session('error'))

        <div class="sg-bita-alert sg-bita-alert--danger">

            <i class="fas fa-exclamation-triangle"></i>

            <span>
                {{ session('error') }}
            </span>

        </div>

    @endif

    @if($errors->any())

        <div class="sg-bita-alert sg-bita-alert--danger">

            <i class="fas fa-circle-exclamation"></i>

            <div>

                <strong>
                    No se pudo realizar la búsqueda.
                </strong>

                <ul class="sg-bita-alert__list">

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
    <section class="sg-bita-summary">

        <article class="sg-bita-summary-card">

            <span class="sg-bita-summary-card__icon">
                <i class="fas fa-list-check"></i>
            </span>

            <div>
                <span class="sg-bita-summary-card__label">
                    Registros consultados
                </span>

                <strong class="sg-bita-summary-card__value">
                    {{ $totalRegistros }}
                </strong>
            </div>

        </article>

        <article class="sg-bita-summary-card">

            <span class="sg-bita-summary-card__icon">
                <i class="fas fa-building-columns"></i>
            </span>

            <div>
                <span class="sg-bita-summary-card__label">
                    Carreras disponibles
                </span>

                <strong class="sg-bita-summary-card__value">
                    {{ $carreras->count() }}
                </strong>
            </div>

        </article>

        <article class="sg-bita-summary-card">

            <span class="sg-bita-summary-card__icon">
                <i class="fas fa-user-shield"></i>
            </span>

            <div>
                <span class="sg-bita-summary-card__label">
                    Alcance
                </span>

                <strong class="sg-bita-summary-card__value sg-bita-summary-card__value--text">
                    Global
                </strong>
            </div>

        </article>

    </section>

    {{-- =====================================================
         FILTROS
    ====================================================== --}}
    <section class="sg-bita-card">

        <header class="sg-bita-card__header">

            <div class="sg-bita-card__heading">

                <span class="sg-bita-card__icon">
                    <i class="fas fa-filter"></i>
                </span>

                <div>

                    <h2 class="sg-bita-card__title">
                        Filtros de búsqueda
                    </h2>

                    <p class="sg-bita-card__subtitle">
                        Utiliza uno o varios criterios para consultar
                        la actividad registrada en todo el sistema.
                    </p>

                </div>

            </div>

        </header>

        <div class="sg-bita-card__body">

            <form
                id="formFiltrosBitacoraSecretariaGeneral"
                method="GET"
                action="{{ route('bitacora.secretaria_general') }}"
                autocomplete="off"
            >

                <div class="sg-bita-filter-grid">

                    {{-- FECHA INICIAL --}}
                    <div class="sg-bita-field">

                        <label
                            for="fecha_inicio"
                            class="sg-bita-label"
                        >
                            Fecha inicial
                        </label>

                        <input
                            type="date"
                            id="fecha_inicio"
                            name="fecha_inicio"
                            class="sg-bita-input"
                            value="{{ request(
                                'fecha_inicio',
                                $filtros['fecha_inicio'] ?? ''
                            ) }}"
                            required
                        >

                        <span
                            id="errorFechaInicioSG"
                            class="sg-bita-error"
                        >
                            Debe seleccionar la fecha inicial.
                        </span>

                    </div>

                    {{-- FECHA FINAL --}}
                    <div class="sg-bita-field">

                        <label
                            for="fecha_fin"
                            class="sg-bita-label"
                        >
                            Fecha final
                        </label>

                        <input
                            type="date"
                            id="fecha_fin"
                            name="fecha_fin"
                            class="sg-bita-input"
                            value="{{ request(
                                'fecha_fin',
                                $filtros['fecha_fin'] ?? ''
                            ) }}"
                            required
                        >

                        <span
                            id="errorFechaFinSG"
                            class="sg-bita-error"
                        >
                            La fecha final no puede ser anterior
                            a la fecha inicial.
                        </span>

                    </div>

                    {{-- CARRERA --}}
                    <div class="sg-bita-field">

                        <label
                            for="id_carrera_contexto"
                            class="sg-bita-label"
                        >
                            Carrera
                        </label>

                        <select
                            id="id_carrera_contexto"
                            name="id_carrera_contexto"
                            class="sg-bita-select"
                            data-sg-bita-optional
                        >

                            <option value="">
                                Todas las carreras
                            </option>

                            @foreach($carreras as $carrera)

                                <option
                                    value="{{ $carrera->id_carrera }}"
                                    @selected(
                                        (string) request(
                                            'id_carrera_contexto'
                                        )
                                        ===
                                        (string) $carrera->id_carrera
                                    )
                                >
                                    {{ $carrera->nombre_carrera }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- USUARIO --}}
                    <div class="sg-bita-field">

                        <label
                            for="id_usuario"
                            class="sg-bita-label"
                        >
                            ID de usuario
                        </label>

                        <input
                            type="number"
                            id="id_usuario"
                            name="id_usuario"
                            class="sg-bita-input"
                            value="{{ request('id_usuario') }}"
                            min="1"
                            step="1"
                            placeholder="Ej. 87"
                            data-sg-bita-optional
                        >

                        <span
                            id="errorUsuarioSG"
                            class="sg-bita-error"
                        >
                            Ingrese un ID de usuario válido.
                        </span>

                    </div>

                    {{-- ROL --}}
                    <div class="sg-bita-field">

                        <label
                            for="id_rol"
                            class="sg-bita-label"
                        >
                            Rol
                        </label>

                        <select
                            id="id_rol"
                            name="id_rol"
                            class="sg-bita-select"
                            data-sg-bita-optional
                        >

                            <option value="">
                                Todos los roles
                            </option>

                            @foreach($rolesDisponibles as $idRol => $nombreRol)

                                <option
                                    value="{{ $idRol }}"
                                    @selected(
                                        (string) request('id_rol')
                                        === (string) $idRol
                                    )
                                >
                                    {{ $nombreRol }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- TRÁMITE --}}
                    <div class="sg-bita-field">

                        <label
                            for="id_tramite"
                            class="sg-bita-label"
                        >
                            Número de trámite
                        </label>

                        <input
                            type="number"
                            id="id_tramite"
                            name="id_tramite"
                            class="sg-bita-input"
                            value="{{ request('id_tramite') }}"
                            min="1"
                            step="1"
                            placeholder="Ej. 146"
                            data-sg-bita-optional
                        >

                        <span
                            id="errorTramiteSG"
                            class="sg-bita-error"
                        >
                            Ingrese un número de trámite válido.
                        </span>

                    </div>

                    {{-- TIPO DE TRÁMITE --}}
                    <div class="sg-bita-field">

                        <label
                            for="tipo_tramite"
                            class="sg-bita-label"
                        >
                            Tipo de trámite
                        </label>

                        <select
                            id="tipo_tramite"
                            name="tipo_tramite"
                            class="sg-bita-select"
                            data-sg-bita-optional
                        >

                            <option value="">
                                Todos los tipos
                            </option>

                            <option
                                value="cambio_carrera"
                                @selected(
                                    request('tipo_tramite')
                                    === 'cambio_carrera'
                                )
                            >
                                Cambio de carrera
                            </option>

                            <option
                                value="cancelacion"
                                @selected(
                                    request('tipo_tramite')
                                    === 'cancelacion'
                                )
                            >
                                Cancelación excepcional
                            </option>

                            <option
                                value="reposicion"
                                @selected(
                                    request('tipo_tramite')
                                    === 'reposicion'
                                )
                            >
                                Reposición
                            </option>

                        </select>

                    </div>

                    {{-- ESTADO --}}
                    <div class="sg-bita-field">

                        <label
                            for="estado"
                            class="sg-bita-label"
                        >
                            Estado
                        </label>

                        <input
                            type="text"
                            id="estado"
                            name="estado"
                            class="sg-bita-input"
                            value="{{ request('estado') }}"
                            maxlength="50"
                            placeholder="Ej. Aprobada"
                            data-sg-bita-optional
                        >

                    </div>

                    {{-- MÓDULO --}}
                    <div class="sg-bita-field">

                        <label
                            for="modulo"
                            class="sg-bita-label"
                        >
                            Módulo
                        </label>

                        <input
                            type="text"
                            id="modulo"
                            name="modulo"
                            class="sg-bita-input"
                            value="{{ request('modulo') }}"
                            maxlength="100"
                            placeholder="Ej. Calendario Académico"
                            data-sg-bita-optional
                        >

                    </div>

                    {{-- ACCIÓN --}}
                    <div class="sg-bita-field">

                        <label
                            for="accion"
                            class="sg-bita-label"
                        >
                            Acción
                        </label>

                        <input
                            type="text"
                            id="accion"
                            name="accion"
                            class="sg-bita-input"
                            value="{{ request('accion') }}"
                            maxlength="100"
                            placeholder="Ej. EMITIR_RESOLUCION"
                            data-sg-bita-optional
                        >

                    </div>

                    {{-- NIVEL --}}
                    <div class="sg-bita-field">

                        <label
                            for="nivel"
                            class="sg-bita-label"
                        >
                            Nivel
                        </label>

                        <select
                            id="nivel"
                            name="nivel"
                            class="sg-bita-select"
                            data-sg-bita-optional
                        >

                            <option value="">
                                Todos los niveles
                            </option>

                            <option
                                value="INFO"
                                @selected(request('nivel') === 'INFO')
                            >
                                Información
                            </option>

                            <option
                                value="ADVERTENCIA"
                                @selected(
                                    request('nivel') === 'ADVERTENCIA'
                                )
                            >
                                Advertencia
                            </option>

                            <option
                                value="ERROR"
                                @selected(request('nivel') === 'ERROR')
                            >
                                Error
                            </option>

                            <option
                                value="SEGURIDAD"
                                @selected(
                                    request('nivel') === 'SEGURIDAD'
                                )
                            >
                                Seguridad
                            </option>

                        </select>

                    </div>

                    {{-- REGISTROS --}}
                    <div class="sg-bita-field">

                        <label
                            for="per_page"
                            class="sg-bita-label"
                        >
                            Registros
                        </label>

                        <select
                            id="per_page"
                            name="per_page"
                            class="sg-bita-select"
                        >

                            @foreach([10, 20, 50, 100] as $cantidad)

                                <option
                                    value="{{ $cantidad }}"
                                    @selected(
                                        (int) request(
                                            'per_page',
                                            20
                                        ) === $cantidad
                                    )
                                >
                                    {{ $cantidad }} registros
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="sg-bita-filter-actions">

                    <button
                        type="submit"
                        id="btnBuscarBitacoraSG"
                        class="sg-bita-button sg-bita-button--search"
                    >
                        <i class="fas fa-search"></i>
                        <span>Buscar</span>
                    </button>

                    <a
                        href="{{ route('bitacora.secretaria_general') }}"
                        class="sg-bita-button sg-bita-button--clear"
                    >
                        <i class="fas fa-eraser"></i>
                        <span>Limpiar filtros</span>
                    </a>

                </div>

            </form>

        </div>

    </section>

    {{-- =====================================================
         TABLA
    ====================================================== --}}
    <section class="sg-bita-card">

        <header
            class="
                sg-bita-card__header
                sg-bita-card__header--between
            "
        >

            <div class="sg-bita-card__heading">

                <span class="sg-bita-card__icon">
                    <i class="fas fa-clipboard-list"></i>
                </span>

                <div>

                    <h2 class="sg-bita-card__title">
                        Registros encontrados
                    </h2>

                    <p class="sg-bita-card__subtitle">
                        Se muestran
                        <strong>{{ $totalRegistros }}</strong>
                        registros del sistema.
                    </p>

                </div>

            </div>

            <span class="sg-bita-context">
                <i class="fas fa-shield-halved"></i>
                Consulta institucional
            </span>

        </header>

        <div class="sg-bita-table-wrapper">

            <table class="sg-bita-table">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Responsable</th>
                        <th>Rol</th>
                        <th>Carrera</th>
                        <th>Trámite</th>
                        <th>Estudiante</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>Nivel</th>
                        <th>Dirección IP</th>
                        <th>Fecha y hora</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($registros as $bitacora)

                        @php
                            $nivel = strtoupper(
                                trim(
                                    (string) (
                                        $bitacora->nivel
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

                            $responsable =
                                $bitacora->usuario_responsable
                                ?? (
                                    isset($bitacora->id_usuario)
                                        ? 'Usuario #'
                                            . $bitacora->id_usuario
                                        : 'No disponible'
                                );

                            $correoResponsable =
                                $bitacora->correo_responsable
                                ?? $bitacora->correo_institucional
                                ?? null;

                            $rol =
                                $bitacora->nombre_rol
                                ?? (
                                    isset($bitacora->id_rol)
                                        ? 'Rol #'
                                            . $bitacora->id_rol
                                        : 'No disponible'
                                );

                            $carrera =
                                $bitacora->carrera_contexto
                                ?? $bitacora->carrera_actor
                                ?? 'No disponible';

                            $tipoTramite =
                                $bitacora->tramite
                                ?? null;

                            if (
                                !$tipoTramite
                                || strtolower(
                                    trim((string) $tipoTramite)
                                ) === 'no aplica'
                            ) {
                                $tipoOriginal = strtolower(
                                    trim(
                                        (string) (
                                            $bitacora
                                                ->tipo_tramite_academico
                                            ?? ''
                                        )
                                    )
                                );

                                $tipoTramite = match($tipoOriginal) {
                                    'cambio_carrera' =>
                                        'Cambio de carrera',

                                    'cancelacion' =>
                                        'Cancelación excepcional',

                                    'reposicion' =>
                                        'Reposición',

                                    default =>
                                        'No aplica',
                                };
                            }

                            $estadoTramite =
                                $bitacora->estado_tramite
                                ?? 'No aplica';

                            $estudiante =
                                $bitacora->estudiante
                                ?? 'No aplica';

                            $correoEstudiante =
                                $bitacora->correo_estudiante
                                ?? null;

                            $modulo =
                                $bitacora->modulo
                                ?? 'General';

                            $accion =
                                $bitacora->accion
                                ?? 'No disponible';

                            $descripcion =
                                $bitacora->descripcion
                                ?? 'Sin descripción disponible.';

                            $direccionIp =
                                $bitacora->ip_address
                                ?? 'No disponible';

                            $userAgent =
                                $bitacora->user_agent
                                ?? 'Información no disponible';

                            $fechaAccion =
                                $bitacora->fecha_accion
                                ?? null;
                        @endphp

                        <tr>

                            <td>
                                <span class="sg-bita-record-id">
                                    #{{ $bitacora->id_bitacora ?? 'N/D' }}
                                </span>
                            </td>

                            <td>

                                <div class="sg-bita-person">

                                    <span class="sg-bita-person__avatar">
                                        <i class="fas fa-user"></i>
                                    </span>

                                    <div class="sg-bita-person__information">

                                        <span class="sg-bita-person__name">
                                            {{ $responsable }}
                                        </span>

                                        @if($correoResponsable)

                                            <span class="sg-bita-person__email">
                                                {{ $correoResponsable }}
                                            </span>

                                        @endif

                                    </div>

                                </div>

                            </td>

                            <td>
                                <span class="sg-bita-tag sg-bita-tag--role">
                                    {{ $rol }}
                                </span>
                            </td>

                            <td>
                                <span class="sg-bita-tag sg-bita-tag--career">
                                    {{ $carrera }}
                                </span>
                            </td>

                            <td>

                                @if($bitacora->id_tramite ?? null)

                                    <span class="sg-bita-record-id">
                                        #{{ $bitacora->id_tramite }}
                                    </span>

                                @else

                                    <span class="sg-bita-muted">
                                        No aplica
                                    </span>

                                @endif

                            </td>

                            <td>

                                @if($estudiante !== 'No aplica')

                                    <div class="sg-bita-student">

                                        <span class="sg-bita-student__name">
                                            {{ $estudiante }}
                                        </span>

                                        @if($correoEstudiante)

                                            <span class="sg-bita-student__email">
                                                {{ $correoEstudiante }}
                                            </span>

                                        @endif

                                    </div>

                                @else

                                    <span class="sg-bita-muted">
                                        No aplica
                                    </span>

                                @endif

                            </td>

                            <td>
                                <span class="sg-bita-tag sg-bita-tag--type">
                                    {{ $tipoTramite }}
                                </span>
                            </td>

                            <td>
                                <span class="sg-bita-tag sg-bita-tag--status">
                                    {{ $estadoTramite }}
                                </span>
                            </td>

                            <td>
                                <span class="sg-bita-tag sg-bita-tag--module">
                                    {{ $modulo }}
                                </span>
                            </td>

                            <td>
                                <span class="sg-bita-tag sg-bita-tag--action">
                                    {{ $accion }}
                                </span>
                            </td>

                            <td class="sg-bita-description">
                                {{ $descripcion }}
                            </td>

                            <td>

                                <span
                                    class="
                                        sg-bita-level
                                        sg-bita-level--{{ $nivelClase }}
                                    "
                                >
                                    {{ $nivel }}
                                </span>

                            </td>

                            <td
                                class="sg-bita-nowrap"
                                title="{{ $userAgent }}"
                            >
                                <i class="fas fa-network-wired"></i>
                                {{ $direccionIp }}
                            </td>

                            <td class="sg-bita-nowrap">

                                @if($fechaAccion)

                                    <i class="far fa-calendar-alt"></i>

                                    {{ \Carbon\Carbon::parse(
                                        $fechaAccion
                                    )->format('d/m/Y H:i:s') }}

                                @else

                                    <span class="sg-bita-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="14">

                                <div class="sg-bita-empty">

                                    <span class="sg-bita-empty__icon">
                                        <i class="fas fa-inbox"></i>
                                    </span>

                                    <h3 class="sg-bita-empty__title">
                                        No se encontraron registros
                                    </h3>

                                    <p class="sg-bita-empty__description">
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

    </section>

</div>

@endsection