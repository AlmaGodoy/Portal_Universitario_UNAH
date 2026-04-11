@extends('layouts.app-estudiantes')

@section('titulo', 'Mis Trámites')

@section('content')

<div class="container-fluid py-4">

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h2 class="fw-bold text-primary mb-1">Mis trámites</h2>
                    <p class="text-muted mb-0">
                        Consulta aquí el historial de tus solicitudes académicas registradas en el sistema.
                    </p>
                </div>

                <div>
                    <span class="badge bg-primary px-3 py-2 rounded-pill fs-6">
                        Total: {{ $total ?? 0 }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($mensajeError))
        <div class="alert alert-danger rounded-4 shadow-sm">
            {{ $mensajeError }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger rounded-4 shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    @if(isset($tramites) && $tramites->count() > 0)

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 px-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">Listado de trámites</h5>
                        <small class="text-muted">
                            Se muestran tus trámites más recientes con la información más importante.
                        </small>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">N° Trámite</th>
                                <th class="px-4 py-3">Tipo</th>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Detalle clave</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tramites as $tramite)
                                @php
                                    $tipoMostrar = $tramite->tipo_tramite_mostrar ?? 'Trámite académico';
                                    $tipoRaw = mb_strtolower(trim((string)($tramite->tipo_tramite_academico ?? $tipoMostrar)), 'UTF-8');

                                    $detallePartes = [];

                                    if (str_contains($tipoRaw, 'cambio')) {
                                        if (!empty($tramite->carrera_destino) && $tramite->carrera_destino !== 'No aplica') {
                                            $detallePartes[] = 'Carrera destino: ' . $tramite->carrera_destino;
                                        } else {
                                            $detallePartes[] = 'Solicitud de cambio de carrera';
                                        }
                                    } elseif (str_contains($tipoRaw, 'cancel')) {
                                        $detallePartes[] = 'Solicitud de cancelación de clases';
                                    } else {
                                        $detallePartes[] = 'Trámite académico registrado';
                                    }

                                    $direccion = trim((string)($tramite->direccion ?? ''));
                                    $direccionNormalizada = mb_strtolower($direccion, 'UTF-8');

                                    if ($direccion !== '' && $direccionNormalizada !== 'sin dirección registrada') {
                                        $detallePartes[] = 'Dirección: ' . $direccion;
                                    }

                                    $detalleClave = implode(' • ', $detallePartes);
                                @endphp

                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-primary">
                                            #{{ $tramite->id_tramite }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="fw-semibold text-dark">
                                            {{ $tipoMostrar }}
                                        </div>
                                        <small class="text-muted">
                                            Trámite académico
                                        </small>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="text-dark fw-semibold">
                                            {{ \Carbon\Carbon::parse($tramite->fecha_solicitud)->format('d/m/Y') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($tramite->fecha_solicitud)->format('h:i A') }}
                                        </small>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="text-dark">
                                            {{ $detalleClave }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <span class="badge {{ $tramite->badge_class ?? 'bg-secondary' }} px-3 py-2 rounded-pill">
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

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-folder-open text-muted mb-3" style="font-size: 3rem;"></i>
                <h4 class="fw-bold mb-2">Aún no tienes trámites registrados</h4>
                <p class="text-muted mb-0">
                    Cuando realices una solicitud académica, aparecerá listada aquí.
                </p>
            </div>
        </div>

    @endif

</div>

@endsection