@extends('layouts.app-coordinador')

@section('title', 'Bitácora de Carrera')
@section('titulo', 'Bitácora de Carrera')

@section('content')

@vite([
    'resources/css/bitacora_coordinador.css',
    'resources/js/bitacora_coordinador.js'
])

@php
    /*
    |--------------------------------------------------------------------------
    | NORMALIZACIÓN DE LOS RESULTADOS
    |--------------------------------------------------------------------------
    |
    | BitacoraService actualmente devuelve una Collection.
    | Este bloque también permite recibir un paginador en el futuro,
    | pero no intenta imprimir enlaces de paginación.
    |
    */

    $fuenteBitacoras = $bitacoras ?? collect();

    if (
        $fuenteBitacoras instanceof
        \Illuminate\Contracts\Pagination\Paginator
    ) {
        $registros = collect(
            $fuenteBitacoras->items()
        );

        $totalRegistros =
            $fuenteBitacoras instanceof
            \Illuminate\Contracts\Pagination\LengthAwarePaginator
                ? $fuenteBitacoras->total()
                : $registros->count();
    } else {
        $registros =
            $fuenteBitacoras instanceof
            \Illuminate\Support\Collection
                ? $fuenteBitacoras
                : collect($fuenteBitacoras);

        $totalRegistros = $registros->count();
    }
@endphp

<div class="coord-bita-page">

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}
    <section class="coord-bita-header">

        <div class="coord-bita-header__content">

            <div class="coord-bita-header__information">

                <span class="coord-bita-header__role">
                    Coordinación de Carrera
                </span>

                <h1 class="coord-bita-header__title">
                    Bitácora de Carrera
                </h1>

                <p class="coord-bita-header__subtitle">
                    Consulta las acciones registradas dentro de la
                    carrera asignada al coordinador.
                </p>

            </div>

            <a
                href="{{ url()->previous() }}"
                class="coord-bita-button coord-bita-button--back"
            >
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>

        </div>

    </section>

    {{-- =====================================================
         MENSAJES
    ====================================================== --}}

    @if(session('success'))

        <div
            class="coord-bita-alert coord-bita-alert--success"
            data-coord-bita-auto-close
        >
            <i class="fas fa-check-circle"></i>

            <span>
                {{ session('success') }}
            </span>
        </div>

    @endif

    @if(session('error'))

        <div class="coord-bita-alert coord-bita-alert--danger">

            <i class="fas fa-exclamation-triangle"></i>

            <span>
                {{ session('error') }}
            </span>

        </div>

    @endif

    @if($errors->any())

        <div class="coord-bita-alert coord-bita-alert--danger">

            <i class="fas fa-exclamation-circle"></i>

            <div>

                <strong>
                    No se pudo realizar la búsqueda.
                </strong>

                <ul class="coord-bita-alert__list">

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
         FILTROS
    ====================================================== --}}
    <section class="coord-bita-card">

        <header class="coord-bita-card__header">

            <div class="coord-bita-card__heading">

                <span class="coord-bita-card__icon">
                    <i class="fas fa-filter"></i>
                </span>

                <div>

                    <h2 class="coord-bita-card__title">
                        Filtros de búsqueda
                    </h2>

                    <p class="coord-bita-card__subtitle">
                        Selecciona uno o varios criterios para consultar
                        los registros correspondientes a la carrera.
                    </p>

                </div>

            </div>

        </header>

        <div class="coord-bita-card__body">

            <form
                id="formFiltrosBitacoraCoordinador"
                method="GET"
                action="{{ route('bitacora.coordinador') }}"
                autocomplete="off"
            >

                <div class="coord-bita-filter-grid">

                    {{-- FECHA INICIAL --}}
                    <div class="coord-bita-field">

                        <label
                            for="fecha_inicio"
                            class="coord-bita-label"
                        >
                            Fecha inicial
                        </label>

                        <input
                            type="date"
                            id="fecha_inicio"
                            name="fecha_inicio"
                            class="coord-bita-input"
                            value="{{ request(
                                'fecha_inicio',
                                $filtros['fecha_inicio'] ?? ''
                            ) }}"
                            required
                        >

                        <span
                            id="errorFechaInicioCoordinador"
                            class="coord-bita-error"
                        >
                            Debe seleccionar la fecha inicial.
                        </span>

                    </div>

                    {{-- FECHA FINAL --}}
                    <div class="coord-bita-field">

                        <label
                            for="fecha_fin"
                            class="coord-bita-label"
                        >
                            Fecha final
                        </label>

                        <input
                            type="date"
                            id="fecha_fin"
                            name="fecha_fin"
                            class="coord-bita-input"
                            value="{{ request(
                                'fecha_fin',
                                $filtros['fecha_fin'] ?? ''
                            ) }}"
                            required
                        >

                        <span
                            id="errorFechaFinCoordinador"
                            class="coord-bita-error"
                        >
                            La fecha final no puede ser anterior a la
                            fecha inicial.
                        </span>

                    </div>

                    {{-- NÚMERO DE TRÁMITE --}}
                    <div class="coord-bita-field">

                        <label
                            for="id_tramite"
                            class="coord-bita-label"
                        >
                            Número de trámite
                        </label>

                        <input
                            type="number"
                            id="id_tramite"
                            name="id_tramite"
                            class="coord-bita-input"
                            value="{{ request('id_tramite') }}"
                            min="1"
                            step="1"
                            inputmode="numeric"
                            placeholder="Ej. 146"
                            data-coord-bita-optional
                        >

                        <span
                            id="errorTramiteCoordinador"
                            class="coord-bita-error"
                        >
                            Ingrese un número entero mayor que cero.
                        </span>

                    </div>

                    {{-- TIPO DE TRÁMITE --}}
                    <div class="coord-bita-field">

                        <label
                            for="tipo_tramite"
                            class="coord-bita-label"
                        >
                            Tipo de trámite
                        </label>

                        <select
                            id="tipo_tramite"
                            name="tipo_tramite"
                            class="coord-bita-select"
                            data-coord-bita-optional
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
                    <div class="coord-bita-field">

                        <label
                            for="estado"
                            class="coord-bita-label"
                        >
                            Estado del trámite
                        </label>

                        <input
                            type="text"
                            id="estado"
                            name="estado"
                            class="coord-bita-input"
                            value="{{ request('estado') }}"
                            maxlength="50"
                            placeholder="Ej. Aprobada"
                            data-coord-bita-optional
                        >

                    </div>

                    {{-- ACCIÓN --}}
                    <div class="coord-bita-field">

                        <label
                            for="accion"
                            class="coord-bita-label"
                        >
                            Acción
                        </label>

                        <input
                            type="text"
                            id="accion"
                            name="accion"
                            class="coord-bita-input"
                            value="{{ request('accion') }}"
                            maxlength="100"
                            placeholder="Ej. EMITIR_RESOLUCION"
                            data-coord-bita-optional
                        >

                    </div>

                    {{-- NIVEL --}}
                    <div class="coord-bita-field">

                        <label
                            for="nivel"
                            class="coord-bita-label"
                        >
                            Nivel
                        </label>

                        <select
                            id="nivel"
                            name="nivel"
                            class="coord-bita-select"
                            data-coord-bita-optional
                        >

                            <option value="">
                                Todos los niveles
                            </option>

                            <option
                                value="INFO"
                                @selected(
                                    request('nivel') === 'INFO'
                                )
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
                                @selected(
                                    request('nivel') === 'ERROR'
                                )
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
                    <div class="coord-bita-field">

                        <label
                            for="per_page"
                            class="coord-bita-label"
                        >
                            Registros
                        </label>

                        <select
                            id="per_page"
                            name="per_page"
                            class="coord-bita-select"
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

                <div class="coord-bita-filter-actions">

                    <button
                        type="submit"
                        id="btnBuscarBitacoraCoordinador"
                        class="
                            coord-bita-button
                            coord-bita-button--search
                        "
                    >
                        <i class="fas fa-search"></i>
                        <span>Buscar</span>
                    </button>

                    <a
                        href="{{ route('bitacora.coordinador') }}"
                        class="
                            coord-bita-button
                            coord-bita-button--clear
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
    <section class="coord-bita-card">

        <header
            class="
                coord-bita-card__header
                coord-bita-card__header--between
            "
        >

            <div class="coord-bita-card__heading">

                <span class="coord-bita-card__icon">
                    <i class="fas fa-clipboard-list"></i>
                </span>

                <div>

                    <h2 class="coord-bita-card__title">
                        Registros encontrados
                    </h2>

                    <p class="coord-bita-card__subtitle">
                        Se muestran
                        <strong>{{ $totalRegistros }}</strong>
                        registros.
                    </p>

                </div>

            </div>

            <span class="coord-bita-context">
                <i class="fas fa-user-tie"></i>
                Carrera asignada
            </span>

        </header>

        <div class="coord-bita-table-wrapper">

            <table class="coord-bita-table">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Responsable</th>
                        <th>Rol</th>
                        <th>Trámite</th>
                        <th>Estudiante</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Carrera</th>
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

                            {{-- ID --}}
                            <td>

                                <span class="coord-bita-record-id">
                                    #{{ $bitacora->id_bitacora ?? 'N/D' }}
                                </span>

                            </td>

                            {{-- RESPONSABLE --}}
                            <td>

                                <div class="coord-bita-person">

                                    <span class="coord-bita-person__avatar">
                                        <i class="fas fa-user"></i>
                                    </span>

                                    <div class="coord-bita-person__info">

                                        <span class="coord-bita-person__name">
                                            {{ $responsable }}
                                        </span>

                                        @if($correoResponsable)

                                            <span
                                                class="
                                                    coord-bita-person__email
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
                                        coord-bita-tag
                                        coord-bita-tag--role
                                    "
                                >
                                    {{ $rol }}
                                </span>

                            </td>

                            {{-- TRÁMITE --}}
                            <td>

                                @if($bitacora->id_tramite ?? null)

                                    <span class="coord-bita-record-id">
                                        #{{ $bitacora->id_tramite }}
                                    </span>

                                @else

                                    <span class="coord-bita-muted">
                                        No aplica
                                    </span>

                                @endif

                            </td>

                            {{-- ESTUDIANTE --}}
                            <td>

                                @if($estudiante !== 'No aplica')

                                    <div class="coord-bita-student">

                                        <span
                                            class="
                                                coord-bita-student__name
                                            "
                                        >
                                            {{ $estudiante }}
                                        </span>

                                        @if($correoEstudiante)

                                            <span
                                                class="
                                                    coord-bita-student__email
                                                "
                                            >
                                                {{ $correoEstudiante }}
                                            </span>

                                        @endif

                                    </div>

                                @else

                                    <span class="coord-bita-muted">
                                        No aplica
                                    </span>

                                @endif

                            </td>

                            {{-- TIPO --}}
                            <td>

                                <span
                                    class="
                                        coord-bita-tag
                                        coord-bita-tag--type
                                    "
                                >
                                    {{ $tipoTramite }}
                                </span>

                            </td>

                            {{-- ESTADO --}}
                            <td>

                                <span
                                    class="
                                        coord-bita-tag
                                        coord-bita-tag--status
                                    "
                                >
                                    {{ $estadoTramite }}
                                </span>

                            </td>

                            {{-- CARRERA --}}
                            <td>

                                <span
                                    class="
                                        coord-bita-tag
                                        coord-bita-tag--career
                                    "
                                >
                                    {{ $carrera }}
                                </span>

                            </td>

                            {{-- MÓDULO --}}
                            <td>

                                <span
                                    class="
                                        coord-bita-tag
                                        coord-bita-tag--module
                                    "
                                >
                                    {{ $modulo }}
                                </span>

                            </td>

                            {{-- ACCIÓN --}}
                            <td>

                                <span
                                    class="
                                        coord-bita-tag
                                        coord-bita-tag--action
                                    "
                                >
                                    {{ $accion }}
                                </span>

                            </td>

                            {{-- DESCRIPCIÓN --}}
                            <td class="coord-bita-description">

                                {{ $descripcion }}

                            </td>

                            {{-- NIVEL --}}
                            <td>

                                <span
                                    class="
                                        coord-bita-level
                                        coord-bita-level--{{ $nivelClase }}
                                    "
                                >
                                    {{ $nivel }}
                                </span>

                            </td>

                            {{-- IP --}}
                            <td
                                class="coord-bita-nowrap"
                                title="{{ $userAgent }}"
                            >

                                <i class="fas fa-network-wired"></i>

                                {{ $direccionIp }}

                            </td>

                            {{-- FECHA --}}
                            <td class="coord-bita-nowrap">

                                @if($fechaAccion)

                                    <i class="far fa-calendar-alt"></i>

                                    {{ \Carbon\Carbon::parse(
                                        $fechaAccion
                                    )->format('d/m/Y H:i:s') }}

                                @else

                                    <span class="coord-bita-muted">
                                        No disponible
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="14">

                                <div class="coord-bita-empty">

                                    <span class="coord-bita-empty__icon">
                                        <i class="fas fa-inbox"></i>
                                    </span>

                                    <h3 class="coord-bita-empty__title">
                                        No se encontraron registros
                                    </h3>

                                    <p class="coord-bita-empty__text">
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