@extends('layouts.app-secretaria')

@section('title', 'Bitácora de Trámites')
@section('titulo', 'Bitácora de Trámites')

@section('content')

<style>
    /*
    |--------------------------------------------------------------------------
    | Contenedor principal
    |--------------------------------------------------------------------------
    */

    .bita-page,
    .bita-page *,
    .bita-page *::before,
    .bita-page *::after {
        box-sizing: border-box;
    }

    .bita-page {
        width: 100%;
        padding: 28px 18px 36px;
        color: #20344f;
        font-family: inherit;
    }

    /*
    |--------------------------------------------------------------------------
    | Encabezado
    |--------------------------------------------------------------------------
    */

    .bita-header {
        margin-bottom: 24px;
        overflow: hidden;
        border: none !important;
        border-radius: 18px !important;
        color: #ffffff !important;
        background: linear-gradient(
            135deg,
            #174a96 0%,
            #2458a6 100%
        ) !important;
        box-shadow: 0 10px 24px rgba(23, 74, 150, 0.18);
    }

    .bita-header__content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        padding: 22px 24px;
        background: transparent !important;
    }

    .bita-header__text {
        min-width: 0;
    }

    .bita-header__role {
        display: block;
        margin-bottom: 5px;
        color: rgba(255, 255, 255, 0.84) !important;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .bita-header__title {
        margin: 0 0 5px;
        color: #ffffff !important;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .bita-header__subtitle {
        max-width: 760px;
        margin: 0;
        color: rgba(255, 255, 255, 0.92) !important;
        font-size: 0.98rem;
        line-height: 1.55;
    }

    /*
    |--------------------------------------------------------------------------
    | Botones
    |--------------------------------------------------------------------------
    */

    .bita-button {
        display: inline-flex !important;
        justify-content: center;
        align-items: center;
        gap: 9px;
        min-height: 46px;
        padding: 10px 18px !important;
        border: 1px solid transparent !important;
        border-radius: 12px !important;
        font-family: inherit;
        font-size: 0.94rem;
        font-weight: 800 !important;
        line-height: 1;
        text-decoration: none !important;
        cursor: pointer;
        box-shadow: none;
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease,
            background-color 0.2s ease,
            color 0.2s ease;
    }

    .bita-button--back {
        border-color: #d7e3f8 !important;
        color: #174a96 !important;
        background: #ffffff !important;
    }

    .bita-button--back:hover,
    .bita-button--back:focus {
        color: #123d7f !important;
        background: #f4f8ff !important;
        transform: translateY(-1px);
    }

    .bita-button--search {
        border: none !important;
        color: #ffffff !important;
        background: linear-gradient(
            135deg,
            #174a96 0%,
            #2458a6 100%
        ) !important;
        box-shadow: 0 10px 18px rgba(23, 74, 150, 0.18) !important;
    }

    .bita-button--search:hover,
    .bita-button--search:focus {
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 12px 22px rgba(23, 74, 150, 0.23) !important;
    }

    .bita-button--search:disabled {
        cursor: wait;
        opacity: 0.72;
        transform: none;
    }

    .bita-button--clear {
        border-color: #cdd9ea !important;
        color: #405574 !important;
        background: #ffffff !important;
    }

    .bita-button--clear:hover,
    .bita-button--clear:focus {
        color: #174a96 !important;
        background: #f4f8ff !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Alertas
    |--------------------------------------------------------------------------
    */

    .bita-alert {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin-bottom: 20px;
        padding: 14px 16px;
        border: 1px solid transparent;
        border-radius: 14px;
        font-size: 0.94rem;
        line-height: 1.5;
    }

    .bita-alert--success {
        border-color: #b7e1c3;
        color: #166534;
        background: #edf9f0;
    }

    .bita-alert--danger {
        border-color: #f2b8b5;
        color: #a93226;
        background: #fff4f3;
    }

    .bita-alert__list {
        margin: 7px 0 0;
        padding-left: 20px;
    }

    /*
    |--------------------------------------------------------------------------
    | Tarjetas
    |--------------------------------------------------------------------------
    */

    .bita-card {
        margin-bottom: 24px;
        overflow: hidden;
        border: 1px solid #dbe5f2 !important;
        border-radius: 18px !important;
        background: #ffffff !important;
        box-shadow: 0 8px 24px rgba(18, 61, 127, 0.06);
    }

    .bita-card__header {
        padding: 14px 20px;
        border-bottom: 1px solid #dbe5f2;
        background: #f7faff !important;
    }

    .bita-card__header--between {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    .bita-card__heading {
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .bita-card__icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        border-radius: 12px;
        color: #174a96;
        background: #eaf3ff;
        font-size: 1.05rem;
    }

    .bita-card__title {
        margin: 0 0 3px;
        color: #163b77 !important;
        font-size: 1.08rem;
        font-weight: 800;
        line-height: 1.3;
    }

    .bita-card__subtitle {
        margin: 0;
        color: #6b7b93 !important;
        font-size: 0.92rem;
        line-height: 1.45;
    }

    .bita-card__body {
        padding: 20px;
        background: #ffffff !important;
    }

    .bita-card__footer {
        padding: 14px 20px;
        border-top: 1px solid #dbe5f2;
        background: #fbfdff;
    }

    .bita-context {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 13px;
        border: 1px solid #cfe0f6;
        border-radius: 999px;
        color: #174a96;
        background: #edf5ff;
        font-size: 0.86rem;
        font-weight: 700;
        white-space: nowrap;
    }

    /*
    |--------------------------------------------------------------------------
    | Formulario de filtros
    |--------------------------------------------------------------------------
    */

    .bita-filter-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(150px, 1fr));
        gap: 18px 14px;
    }

    .bita-field {
        min-width: 0;
    }

    .bita-label {
        display: block;
        margin-bottom: 8px;
        color: #143c79 !important;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .bita-input,
    .bita-select {
        width: 100% !important;
        min-height: 46px !important;
        padding: 10px 12px !important;
        border: 1px solid #cdd9ea !important;
        border-radius: 12px !important;
        outline: none !important;
        color: #20344f !important;
        background: #ffffff !important;
        font-family: inherit;
        font-size: 0.92rem;
        box-shadow: none !important;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .bita-input::placeholder {
        color: #96a4b8;
    }

    .bita-input:focus,
    .bita-select:focus {
        border-color: #2458a6 !important;
        box-shadow: 0 0 0 3px rgba(36, 88, 166, 0.1) !important;
    }

    .bita-input.bita-invalid,
    .bita-select.bita-invalid {
        border-color: #c0392b !important;
        box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.1) !important;
    }

    .bita-error {
        display: none;
        margin-top: 7px;
        color: #b42318;
        font-size: 0.82rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .bita-error.bita-error--visible {
        display: block;
    }

    .bita-filter-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-top: 22px;
    }

    /*
    |--------------------------------------------------------------------------
    | Tabla
    |--------------------------------------------------------------------------
    */

    .bita-table-wrapper {
        width: 100%;
        overflow-x: auto;
        background: #ffffff !important;
    }

    .bita-table {
        width: 100% !important;
        min-width: 1650px;
        margin: 0 !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        color: #20344f !important;
        background: #ffffff !important;
    }

    .bita-table thead,
    .bita-table thead tr,
    .bita-table thead th {
        color: #143c79 !important;
        background: #f7faff !important;
    }

    .bita-table thead th {
        padding: 14px 15px !important;
        border: none !important;
        border-bottom: 1px solid #dbe5f2 !important;
        font-size: 0.8rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.02em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .bita-table tbody tr,
    .bita-table tbody td {
        background: #ffffff !important;
    }

    .bita-table tbody td {
        padding: 14px 15px !important;
        border: none !important;
        border-bottom: 1px solid #e7edf5 !important;
        color: #3c506d !important;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .bita-table tbody tr:hover td {
        background: #f8fbff !important;
    }

    .bita-record-id {
        color: #174a96;
        font-weight: 800;
        white-space: nowrap;
    }

    .bita-user {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 180px;
    }

    .bita-user__avatar {
        width: 34px;
        height: 34px;
        min-width: 34px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        color: #174a96;
        background: #edf5ff;
    }

    .bita-user__name {
        color: #20344f;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .bita-tag {
        display: inline-flex;
        align-items: center;
        max-width: 250px;
        padding: 6px 10px;
        border: 1px solid transparent;
        border-radius: 9px;
        font-size: 0.8rem;
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .bita-tag--blue {
        border-color: #d6e2f2;
        color: #2458a6;
        background: #edf5ff;
    }

    .bita-tag--gray {
        border-color: #e1e7ef;
        color: #4d607c;
        background: #f5f7fa;
    }

    .bita-tag--module {
        border-color: #d6e2f2;
        color: #174a96;
        background: #f7faff;
    }

    .bita-tag--action {
        color: #705500;
        background: #fff8d9;
    }

    .bita-description {
        min-width: 280px;
        max-width: 440px;
        color: #52647e !important;
        line-height: 1.55;
        white-space: normal;
    }

    .bita-level {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-width: 92px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .bita-level--info {
        color: #174a96;
        background: #eaf3ff;
    }

    .bita-level--advertencia {
        color: #8a6500;
        background: #fff4c7;
    }

    .bita-level--error {
        color: #a93226;
        background: #ffe9e7;
    }

    .bita-level--seguridad {
        color: #5d3a9b;
        background: #f0e9ff;
    }

    .bita-level--critico {
        color: #8b1e18;
        background: #ffdeda;
    }

    .bita-nowrap {
        white-space: nowrap;
    }

    .bita-muted {
        color: #8a98ac;
    }

    /*
    |--------------------------------------------------------------------------
    | Estado vacío
    |--------------------------------------------------------------------------
    */

    .bita-empty {
        padding: 50px 20px;
        text-align: center;
        background: #ffffff !important;
    }

    .bita-empty__icon {
        display: block;
        margin-bottom: 12px;
        color: #9badc5;
        font-size: 2.5rem;
    }

    .bita-empty__title {
        margin: 0 0 7px;
        color: #163b77;
        font-size: 1.15rem;
        font-weight: 800;
    }

    .bita-empty__text {
        margin: 0;
        color: #6b7b93;
        line-height: 1.55;
    }

    /*
    |--------------------------------------------------------------------------
    | Paginación
    |--------------------------------------------------------------------------
    */

    .bita-card__footer .pagination {
        margin: 0;
    }

    .bita-card__footer .page-link {
        margin: 0 3px;
        border: 1px solid #dbe5f2;
        border-radius: 9px;
        color: #174a96;
        background: #ffffff;
        box-shadow: none;
    }

    .bita-card__footer .page-item.active .page-link {
        border-color: #174a96;
        color: #ffffff;
        background: #174a96;
    }

    .bita-card__footer .page-link:hover {
        color: #174a96;
        background: #edf5ff;
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1399.98px) {
        .bita-filter-grid {
            grid-template-columns: repeat(4, minmax(160px, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .bita-page {
            padding: 20px 12px 30px;
        }

        .bita-header__content,
        .bita-card__header--between {
            flex-direction: column;
            align-items: flex-start;
        }

        .bita-header__title {
            font-size: 1.6rem;
        }

        .bita-filter-grid {
            grid-template-columns: repeat(2, minmax(160px, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .bita-page {
            padding: 16px 8px 26px;
        }

        .bita-header__content {
            padding: 20px;
        }

        .bita-filter-grid {
            grid-template-columns: 1fr;
        }

        .bita-card__body {
            padding: 16px;
        }

        .bita-card__header {
            padding: 14px 16px;
        }

        .bita-filter-actions,
        .bita-button,
        .bita-button--back {
            width: 100%;
        }

        .bita-card__heading {
            align-items: flex-start;
        }
    }
</style>

<main class="bita-page">

    {{-- Encabezado --}}
    <section class="bita-header">
        <div class="bita-header__content">
            <div class="bita-header__text">
                <span class="bita-header__role">
                    Secretaría de Carrera
                </span>

                <h1 class="bita-header__title">
                    Bitácora de Trámites
                </h1>

                <p class="bita-header__subtitle">
                    Consulta las acciones realizadas sobre los trámites
                    correspondientes a la carrera asignada.
                </p>
            </div>

            <a
                href="{{ url()->previous() }}"
                class="bita-button bita-button--back"
            >
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>
        </div>
    </section>

    {{-- Alertas --}}
    @if (session('success'))
        <div
            class="bita-alert bita-alert--success"
            data-bita-auto-close
        >
            <i class="fas fa-circle-check"></i>

            <span>
                {{ session('success') }}
            </span>
        </div>
    @endif

    @if (session('error'))
        <div class="bita-alert bita-alert--danger">
            <i class="fas fa-triangle-exclamation"></i>

            <span>
                {{ session('error') }}
            </span>
        </div>
    @endif

    @if ($errors->any())
        <div class="bita-alert bita-alert--danger">
            <i class="fas fa-circle-exclamation"></i>

            <div>
                <strong>
                    No se pudo realizar la búsqueda:
                </strong>

                <ul class="bita-alert__list">
                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Filtros --}}
    <section class="bita-card">
        <header class="bita-card__header">
            <div class="bita-card__heading">
                <span class="bita-card__icon">
                    <i class="fas fa-filter"></i>
                </span>

                <div>
                    <h2 class="bita-card__title">
                        Filtros de búsqueda
                    </h2>

                    <p class="bita-card__subtitle">
                        Selecciona uno o varios criterios para consultar los
                        registros de la carrera.
                    </p>
                </div>
            </div>
        </header>

        <div class="bita-card__body">
            <form
                id="formFiltrosBitacora"
                method="GET"
                action="{{ route('bitacora.secretaria_carrera') }}"
                autocomplete="off"
                novalidate
            >
                <div class="bita-filter-grid">

                    <div class="bita-field">
                        <label
                            for="fecha_inicio"
                            class="bita-label"
                        >
                            Fecha inicial
                        </label>

                        <input
                            type="date"
                            id="fecha_inicio"
                            name="fecha_inicio"
                            class="bita-input"
                            value="{{ request(
                                'fecha_inicio',
                                $filtros['fecha_inicio'] ?? ''
                            ) }}"
                            required
                        >
                    </div>

                    <div class="bita-field">
                        <label
                            for="fecha_fin"
                            class="bita-label"
                        >
                            Fecha final
                        </label>

                        <input
                            type="date"
                            id="fecha_fin"
                            name="fecha_fin"
                            class="bita-input"
                            value="{{ request(
                                'fecha_fin',
                                $filtros['fecha_fin'] ?? ''
                            ) }}"
                            required
                        >

                        <span
                            id="errorFechasBitacora"
                            class="bita-error"
                        >
                            La fecha final no puede ser anterior a la inicial.
                        </span>
                    </div>

                    <div class="bita-field">
                        <label
                            for="id_tramite"
                            class="bita-label"
                        >
                            Número de trámite
                        </label>

                        <input
                            type="number"
                            id="id_tramite"
                            name="id_tramite"
                            class="bita-input"
                            value="{{ request('id_tramite') }}"
                            min="1"
                            step="1"
                            placeholder="Ej. 146"
                            data-bita-optional
                        >

                        <span
                            id="errorTramiteBitacora"
                            class="bita-error"
                        >
                            Debe ingresar un entero mayor que cero.
                        </span>
                    </div>

                    <div class="bita-field">
                        <label
                            for="tipo_tramite"
                            class="bita-label"
                        >
                            Tipo de trámite
                        </label>

                        <select
                            id="tipo_tramite"
                            name="tipo_tramite"
                            class="bita-select"
                            data-bita-optional
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
                                Cancelación
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

                    <div class="bita-field">
                        <label
                            for="estado"
                            class="bita-label"
                        >
                            Estado
                        </label>

                        <input
                            type="text"
                            id="estado"
                            name="estado"
                            class="bita-input"
                            value="{{ request('estado') }}"
                            maxlength="50"
                            placeholder="Ej. Pendiente"
                            data-bita-optional
                        >
                    </div>

                    <div class="bita-field">
                        <label
                            for="accion"
                            class="bita-label"
                        >
                            Acción
                        </label>

                        <input
                            type="text"
                            id="accion"
                            name="accion"
                            class="bita-input"
                            value="{{ request('accion') }}"
                            maxlength="100"
                            placeholder="Ej. REVISAR"
                            data-bita-optional
                        >
                    </div>

                    <div class="bita-field">
                        <label
                            for="nivel"
                            class="bita-label"
                        >
                            Nivel
                        </label>

                        <select
                            id="nivel"
                            name="nivel"
                            class="bita-select"
                            data-bita-optional
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

                            <option
                                value="CRITICO"
                                @selected(
                                    request('nivel') === 'CRITICO'
                                )
                            >
                                Crítico
                            </option>
                        </select>
                    </div>

                    <div class="bita-field">
                        <label
                            for="per_page"
                            class="bita-label"
                        >
                            Registros
                        </label>

                        <select
                            id="per_page"
                            name="per_page"
                            class="bita-select"
                        >
                            @foreach ([10, 20, 50, 100] as $cantidad)
                                <option
                                    value="{{ $cantidad }}"
                                    @selected(
                                        (int) request(
                                            'per_page',
                                            20
                                        ) === $cantidad
                                    )
                                >
                                    {{ $cantidad }} por página
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="bita-filter-actions">
                    <button
                        type="submit"
                        id="btnBuscarBitacora"
                        class="bita-button bita-button--search"
                    >
                        <i class="fas fa-search"></i>
                        <span>Buscar</span>
                    </button>

                    <a
                        href="{{ route(
                            'bitacora.secretaria_carrera'
                        ) }}"
                        class="bita-button bita-button--clear"
                    >
                        <i class="fas fa-eraser"></i>
                        <span>Limpiar filtros</span>
                    </a>
                </div>
            </form>
        </div>
    </section>

    {{-- Registros --}}
    <section class="bita-card">
        <header class="bita-card__header bita-card__header--between">
            <div class="bita-card__heading">
                <span class="bita-card__icon">
                    <i class="fas fa-clipboard-list"></i>
                </span>

                <div>
                    <h2 class="bita-card__title">
                        Registros encontrados
                    </h2>

                    <p class="bita-card__subtitle">
                        Se muestran
                        <strong>{{ count($bitacoras) }}</strong>
                        registros en esta página.
                    </p>
                </div>
            </div>

            <span class="bita-context">
                <i class="fas fa-graduation-cap"></i>
                Carrera asignada
            </span>
        </header>

        <div class="bita-table-wrapper">
            <table class="bita-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Trámite</th>
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
                    @forelse ($bitacoras as $item)
                        @php
                            $fechaRegistro =
                                $item->fecha_accion
                                ?? $item->fecha
                                ?? null;

                            $nivel = strtolower(
                                $item->nivel ?? 'info'
                            );

                            $usuarioResponsable =
                                $item->usuario_responsable
                                ?? $item->correo_institucional
                                ?? (
                                    isset($item->id_usuario)
                                        ? 'Usuario #' . $item->id_usuario
                                        : 'No disponible'
                                );

                            $tipoTramite =
                                strtolower(
                                    $item->tipo_tramite_academico
                                    ?? $item->tipo_tramite
                                    ?? ''
                                );

                            $tipoTramiteTexto = match ($tipoTramite) {
                                'cambio_carrera' =>
                                    'Cambio de carrera',

                                'cancelacion' =>
                                    'Cancelación',

                                'reposicion' =>
                                    'Reposición',

                                default =>
                                    $tipoTramite !== ''
                                        ? ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $tipoTramite
                                            )
                                        )
                                        : 'No disponible',
                            };

                            $estadoTramite =
                                $item->estado_tramite
                                ?? $item->estado
                                ?? 'No disponible';

                            $direccionIp =
                                $item->ip_address
                                ?? 'No disponible';

                            $navegador =
                                $item->user_agent
                                ?? 'Información no disponible';
                        @endphp

                        <tr>
                            <td>
                                <span class="bita-record-id">
                                    #{{ $item->id_bitacora ?? 'N/D' }}
                                </span>
                            </td>

                            <td>
                                <div class="bita-user">
                                    <span class="bita-user__avatar">
                                        <i class="fas fa-user"></i>
                                    </span>

                                    <span class="bita-user__name">
                                        {{ $usuarioResponsable }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                {{ $item->nombre_rol
                                    ?? $item->id_rol
                                    ?? 'No disponible' }}
                            </td>

                            <td>
                                @if ($item->id_tramite ?? null)
                                    <span class="bita-record-id">
                                        #{{ $item->id_tramite }}
                                    </span>
                                @else
                                    <span class="bita-muted">
                                        No aplica
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="bita-tag bita-tag--blue">
                                    {{ $tipoTramiteTexto }}
                                </span>
                            </td>

                            <td>
                                <span class="bita-tag bita-tag--gray">
                                    {{ $estadoTramite }}
                                </span>
                            </td>

                            <td>
                                <span class="bita-tag bita-tag--module">
                                    {{ $item->modulo ?? 'General' }}
                                </span>
                            </td>

                            <td>
                                <span class="bita-tag bita-tag--action">
                                    {{ $item->accion
                                        ?? 'No disponible' }}
                                </span>
                            </td>

                            <td class="bita-description">
                                {{ $item->descripcion
                                    ?? 'Sin descripción disponible.' }}
                            </td>

                            <td>
                                <span class="bita-level bita-level--{{ $nivel }}">
                                    {{ strtoupper($nivel) }}
                                </span>
                            </td>

                            <td
                                class="bita-nowrap"
                                title="{{ $navegador }}"
                            >
                                <i class="fas fa-network-wired"></i>
                                {{ $direccionIp }}
                            </td>

                            <td class="bita-nowrap">
                                @if ($fechaRegistro)
                                    <i class="far fa-calendar-alt"></i>

                                    {{ \Carbon\Carbon::parse(
                                        $fechaRegistro
                                    )->format('d/m/Y H:i:s') }}
                                @else
                                    <span class="bita-muted">
                                        No disponible
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12">
                                <div class="bita-empty">
                                    <span class="bita-empty__icon">
                                        <i class="fas fa-inbox"></i>
                                    </span>

                                    <h3 class="bita-empty__title">
                                        No se encontraron registros
                                    </h3>

                                    <p class="bita-empty__text">
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

        @if ($bitacoras->hasPages())
            <footer class="bita-card__footer">
                {{ $bitacoras->links() }}
            </footer>
        @endif
    </section>

</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formulario = document.getElementById(
            'formFiltrosBitacora'
        );

        if (!formulario) {
            return;
        }

        const fechaInicio = document.getElementById(
            'fecha_inicio'
        );

        const fechaFin = document.getElementById(
            'fecha_fin'
        );

        const idTramite = document.getElementById(
            'id_tramite'
        );

        const errorFechas = document.getElementById(
            'errorFechasBitacora'
        );

        const errorTramite = document.getElementById(
            'errorTramiteBitacora'
        );

        const botonBuscar = document.getElementById(
            'btnBuscarBitacora'
        );

        const camposOpcionales =
            formulario.querySelectorAll(
                '[data-bita-optional]'
            );

        const contenidoOriginalBoton = botonBuscar
            ? botonBuscar.innerHTML
            : '';

        const mostrarError = function (
            campo,
            mensaje,
            mostrar
        ) {
            if (campo) {
                campo.classList.toggle(
                    'bita-invalid',
                    mostrar
                );
            }

            if (mensaje) {
                mensaje.classList.toggle(
                    'bita-error--visible',
                    mostrar
                );
            }
        };

        const actualizarFechaMinima = function () {
            if (!fechaInicio || !fechaFin) {
                return;
            }

            fechaFin.min = fechaInicio.value || '';
        };

        const validarFechas = function () {
            if (!fechaInicio || !fechaFin) {
                return true;
            }

            fechaInicio.classList.remove(
                'bita-invalid'
            );

            fechaFin.classList.remove(
                'bita-invalid'
            );

            if (
                fechaInicio.value === ''
                || fechaFin.value === ''
            ) {
                if (fechaInicio.value === '') {
                    fechaInicio.classList.add(
                        'bita-invalid'
                    );
                }

                if (fechaFin.value === '') {
                    fechaFin.classList.add(
                        'bita-invalid'
                    );
                }

                if (errorFechas) {
                    errorFechas.classList.remove(
                        'bita-error--visible'
                    );
                }

                return false;
            }

            const rangoValido =
                fechaFin.value >= fechaInicio.value;

            mostrarError(
                fechaFin,
                errorFechas,
                !rangoValido
            );

            return rangoValido;
        };

        const validarTramite = function () {
            if (!idTramite) {
                return true;
            }

            const valor = idTramite.value.trim();

            if (valor === '') {
                mostrarError(
                    idTramite,
                    errorTramite,
                    false
                );

                return true;
            }

            const numero = Number(valor);

            const valido =
                Number.isInteger(numero)
                && numero > 0;

            mostrarError(
                idTramite,
                errorTramite,
                !valido
            );

            return valido;
        };

        const limpiarTextos = function () {
            formulario
                .querySelectorAll(
                    'input[type="text"]'
                )
                .forEach(function (campo) {
                    campo.value =
                        campo.value.trim();
                });
        };

        const deshabilitarVacios = function () {
            camposOpcionales.forEach(
                function (campo) {
                    const valor = String(
                        campo.value || ''
                    ).trim();

                    if (valor === '') {
                        campo.disabled = true;
                    }
                }
            );
        };

        const habilitarOpcionales = function () {
            camposOpcionales.forEach(
                function (campo) {
                    campo.disabled = false;
                }
            );
        };

        const mostrarCarga = function () {
            if (!botonBuscar) {
                return;
            }

            botonBuscar.disabled = true;

            botonBuscar.innerHTML =
                '<i class="fas fa-spinner fa-spin"></i>'
                + '<span>Buscando...</span>';
        };

        const restaurarBoton = function () {
            habilitarOpcionales();

            if (!botonBuscar) {
                return;
            }

            botonBuscar.disabled = false;
            botonBuscar.innerHTML =
                contenidoOriginalBoton;
        };

        actualizarFechaMinima();
        validarTramite();

        if (fechaInicio) {
            fechaInicio.addEventListener(
                'change',
                function () {
                    actualizarFechaMinima();
                    validarFechas();
                }
            );

            fechaInicio.addEventListener(
                'input',
                validarFechas
            );
        }

        if (fechaFin) {
            fechaFin.addEventListener(
                'change',
                validarFechas
            );

            fechaFin.addEventListener(
                'input',
                validarFechas
            );
        }

        if (idTramite) {
            idTramite.addEventListener(
                'input',
                validarTramite
            );
        }

        formulario.addEventListener(
            'submit',
            function (event) {
                limpiarTextos();

                const fechasValidas =
                    validarFechas();

                const tramiteValido =
                    validarTramite();

                if (
                    !fechasValidas
                    || !tramiteValido
                ) {
                    event.preventDefault();

                    if (!fechasValidas) {
                        if (
                            fechaInicio
                            && fechaInicio.value === ''
                        ) {
                            fechaInicio.focus();
                        } else if (fechaFin) {
                            fechaFin.focus();
                        }

                        return;
                    }

                    if (idTramite) {
                        idTramite.focus();
                    }

                    return;
                }

                deshabilitarVacios();
                mostrarCarga();
            }
        );

        window.addEventListener(
            'pageshow',
            restaurarBoton
        );

        document
            .querySelectorAll(
                '[data-bita-auto-close]'
            )
            .forEach(function (alerta) {
                window.setTimeout(
                    function () {
                        alerta.style.transition =
                            'opacity 0.3s ease';

                        alerta.style.opacity = '0';

                        window.setTimeout(
                            function () {
                                alerta.remove();
                            },
                            300
                        );
                    },
                    4500
                );
            });
    });
</script>

@endsection