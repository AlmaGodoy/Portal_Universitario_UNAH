@php
    use Illuminate\Support\Facades\Route;

    $dashboardUrl = Route::has('dashboard')
        ? route('dashboard')
        : url('/dashboard');

    $misTramitesJsonUrl = Route::has('mis.tramites.json')
        ? route('mis.tramites.json')
        : url('/mis-tramites/json');
@endphp

@extends('layouts.app-estudiantes')

@section('titulo', 'Mis Trámites')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/mis_tramites.css') }}">
@endpush

@section('content')

<div id="misTramitesPage"
     class="container-fluid mt-page"
     data-url-mis-tramites="{{ $misTramitesJsonUrl }}">

    {{-- Encabezado --}}
    <div class="card mt-header border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="mt-header__top">
                <div class="mt-header__copy">
                    <h1 class="mt-title">Mis trámites</h1>
                    <p class="mt-subtitle mb-0">
                        Consulta aquí el historial de tus solicitudes académicas registradas en el sistema.
                    </p>
                </div>

                <div class="mt-header__actions">
                    <a href="{{ $dashboardUrl }}" class="btn mt-btn-back">
                        <i class="fas fa-arrow-left me-2"></i>
                        Volver al dashboard
                    </a>

                    <span class="mt-total-badge" id="totalTramitesBadge">
                        Total: {{ $total ?? 0 }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas --}}
    <div id="mensajeErrorWrap">
        @if(!empty($mensajeError))
            <div class="alert mt-alert mt-alert-danger shadow-sm">
                <i class="fas fa-triangle-exclamation me-2"></i>
                {{ $mensajeError }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert mt-alert mt-alert-danger shadow-sm">
                <i class="fas fa-triangle-exclamation me-2"></i>
                {{ session('error') }}
            </div>
        @endif
    </div>

    <div id="misTramitesContenido">
        @if(isset($tramites) && $tramites->count() > 0)

            <div class="card mt-card border-0 shadow-sm overflow-hidden">
                <div class="card-header mt-card__header">
                    <div>
                        <h3 class="mt-card__title mb-1">
                            <i class="fas fa-folder-open me-2"></i>
                            Listado de trámites
                        </h3>
                        <p class="mt-card__subtitle mb-0">
                            Se muestran tus trámites más recientes con la información más importante.
                        </p>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="mt-table-wrapper">
                        <table class="table mt-table align-middle table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>N° Trámite</th>
                                    <th>Tipo</th>
                                    <th>Fecha</th>
                                    <th>Detalle clave</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>

                            <tbody id="tablaMisTramitesBody">
                                @foreach($tramites as $tramite)
                                    <tr>
                                        <td>
                                            <div class="mt-tramite-id">
                                                #{{ $tramite->id_tramite }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="mt-table-strong">
                                                {{ $tramite->tipo_tramite_mostrar ?? 'Trámite académico' }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="mt-table-strong">
                                                {{ \Carbon\Carbon::parse($tramite->fecha_solicitud)->format('d/m/Y') }}
                                            </div>
                                            <small>
                                                {{ \Carbon\Carbon::parse($tramite->fecha_solicitud)->format('h:i A') }}
                                            </small>
                                        </td>

                                        <td>
                                            <div class="mt-table-text">
                                                {{ $tramite->detalle_clave ?? 'Trámite académico registrado' }}
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <span class="mt-badge {{ $tramite->badge_class ?? 'bg-secondary' }}">
                                                {{ $tramite->estado_mostrar ?? 'Pendiente' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        @else

            <div class="card mt-card border-0 shadow-sm" id="sinTramitesCard">
                <div class="card-body">
                    <div class="mt-empty">
                        <i class="fas fa-folder-open"></i>
                        <h4>Aún no tienes trámites registrados</h4>
                        <p>
                            Cuando realices una solicitud académica, aparecerá listada aquí.
                        </p>
                    </div>
                </div>
            </div>

            <div class="card mt-card border-0 shadow-sm overflow-hidden d-none" id="tablaTramitesCard">
                <div class="card-header mt-card__header">
                    <div>
                        <h3 class="mt-card__title mb-1">
                            <i class="fas fa-folder-open me-2"></i>
                            Listado de trámites
                        </h3>
                        <p class="mt-card__subtitle mb-0">
                            Se muestran tus trámites más recientes con la información más importante.
                        </p>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="mt-table-wrapper">
                        <table class="table mt-table align-middle table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>N° Trámite</th>
                                    <th>Tipo</th>
                                    <th>Fecha</th>
                                    <th>Detalle clave</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>

                            <tbody id="tablaMisTramitesBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        @endif
    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/mis_tramites.js') }}"></script>
@endpush