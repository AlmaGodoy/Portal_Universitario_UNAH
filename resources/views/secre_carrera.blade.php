@extends('layouts.app-secretaria')

@section('titulo', $titulo ?? 'Secretaría de Carrera')

@section('content')

    {{-- BREADCRUMB --}}
    <div style="padding:8px 18px 0;">
        <div class="breadcrumb-wrap">
            <i class="fas fa-home" style="color:var(--blue2);"></i>
            <a href="{{ route('empleado.dashboard') }}">Inicio</a>
            <span><i class="fas fa-chevron-right" style="font-size:.65rem;color:#bbb;"></i></span>
            <span>{{ $titulo ?? 'Secretaría de Carrera' }}</span>
        </div>
    </div>

    <section class="content" style="padding:0 18px 80px;">

        {{-- BANNER DE LA FACULTAD --}}
        <div class="faculty-banner">
            <div class="fb-bg"></div>
            <div class="fb-photo-right" style="background-image: url('{{ asset('images/FCEAC.jpeg') }}');"></div>
            <div class="fb-content">
                <div>
                    <div class="fb-title-main">
                        Facultad de Ciencias Económicas,<br>Administrativas y Contables
                    </div>
                    <div class="fb-subtitle">FCEAC · UNAH · PumaGestión</div>
                </div>
            </div>
        </div>

        {{-- BARRA DE BÚSQUEDA / USUARIO --}}
        <div class="top-search-row">
            <div class="tsr-input-wrap">
                <input type="text" placeholder="Buscar trámite o estudiante..." id="top-search">
            </div>
            <div class="tsr-user">
                <div class="tsr-avatar" id="top-initials">
                    {{ strtoupper(substr($userName ?? auth()->user()->name ?? 'S', 0, 1)) }}{{ strtoupper(substr(explode(' ', $userName ?? auth()->user()->name ?? 'SE')[1] ?? '', 0, 1)) }}
                </div>
                <span class="tsr-name" id="top-username">{{ $userName ?? auth()->user()->name ?? 'Secretaría' }}</span>
            </div>
        </div>

        {{-- ESTADÍSTICAS --}}
        <div class="info-boxes-row">
            <div class="info-box-custom ibc-dark">
                <i class="fas fa-calendar-check ibc-icon-bg"></i>
                <div class="ibc-top">
                    <i class="fas fa-calendar-check ibc-icon-front"></i>
                    <div>
                        <div class="ibc-num">{{ $tramitesAprobados ?? 312 }}</div>
                        <div class="ibc-label">Trámites Aprobados</div>
                    </div>
                </div>
                <div class="ibc-footer">{{ $userRole ?? 'Secretaría de Carrera' }}</div>
            </div>

            <div class="info-box-custom" style="background:#6f42c1;">
                <i class="fas fa-calendar-days ibc-icon-bg"></i>
                <div class="ibc-top">
                    <i class="fas fa-calendar-days ibc-icon-front"></i>
                    <div>
                        <div class="ibc-num">{{ $fechasActivas ?? 4 }}</div>
                        <div class="ibc-label">Períodos Activos</div>
                    </div>
                </div>
                <div class="ibc-footer">{{ $userRole ?? 'Secretaría de Carrera' }}</div>
            </div>
        </div>

        {{-- PANEL DE REVISIÓN DE DOCUMENTOS --}}
        <div class="card shadow-sm" style="border-radius:15px; border:none; overflow:hidden; margin-top:10px;">
            <div class="card-header" style="background: var(--blue-unah, #003c71); color:white; border-radius:15px 15px 0 0;">
                <h3 class="card-title mb-0">
                    <i class="fas fa-file-circle-check mr-2"></i> Revisión de Documentos
                </h3>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background:#f8f9fa;">
                            <tr>
                                <th style="padding:14px 16px;">Cuenta</th>
                                <th style="padding:14px 16px;">Estudiante</th>
                                <th style="padding:14px 16px;">Tipo de Documento</th>
                                <th style="padding:14px 16px;">Fecha Recibido</th>
                                <th class="text-center" style="padding:14px 16px; width:140px;">Estado</th>
                                <th class="text-center" style="padding:14px 16px; width:120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documentos ?? [] as $doc)
                                @php
                                    $estado      = $doc['estado'] ?? 'pendiente';
                                    $estadoClass = match(strtolower($estado)) {
                                        'aprobado'  => 'badge-success',
                                        'rechazado' => 'badge-danger',
                                        default     => 'badge-warning',
                                    };
                                @endphp
                                <tr>
                                    <td style="padding:14px 16px;">{{ $doc['cuenta'] ?? '' }}</td>
                                    <td style="padding:14px 16px;"><b>{{ $doc['estudiante'] ?? '' }}</b></td>
                                    <td style="padding:14px 16px;">{{ $doc['tipo_documento'] ?? '' }}</td>
                                    <td style="padding:14px 16px;">{{ $doc['fecha'] ?? '' }}</td>
                                    <td class="text-center" style="padding:14px 16px;">
                                        <span class="badge {{ $estadoClass }}" style="font-size:12px; padding:6px 10px;">
                                            {{ ucfirst($estado) }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="padding:14px 16px;">
                                        <button type="button" class="btn btn-sm btn-outline-primary mr-1" title="Ver Documento">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success" title="Aprobar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted" style="padding:20px;">
                                        No hay documentos pendientes de revisión.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PANEL DE FECHAS ACTIVAS --}}
        <div class="card shadow-sm" style="border-radius:15px; border:none; overflow:hidden; margin-top:20px;">
            <div class="card-header" style="background: #6f42c1; color:white; border-radius:15px 15px 0 0;">
                <h3 class="card-title mb-0">
                    <i class="fas fa-calendar-days mr-2"></i> Períodos y Fechas Activas
                </h3>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background:#f8f9fa;">
                            <tr>
                                <th style="padding:14px 16px;">Trámite</th>
                                <th style="padding:14px 16px;">Fecha Inicio</th>
                                <th style="padding:14px 16px;">Fecha Fin</th>
                                <th class="text-center" style="padding:14px 16px; width:130px;">Estado</th>
                                <th class="text-center" style="padding:14px 16px; width:100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($periodos ?? [] as $periodo)
                                @php
                                    $activo      = $periodo['activo'] ?? true;
                                    $estadoLabel = $activo ? 'Activo' : 'Cerrado';
                                    $estadoClass = $activo ? 'badge-success' : 'badge-secondary';
                                @endphp
                                <tr>
                                    <td style="padding:14px 16px;"><b>{{ $periodo['tramite'] ?? '' }}</b></td>
                                    <td style="padding:14px 16px;">{{ $periodo['fecha_inicio'] ?? '' }}</td>
                                    <td style="padding:14px 16px;">{{ $periodo['fecha_fin'] ?? '' }}</td>
                                    <td class="text-center" style="padding:14px 16px;">
                                        <span class="badge {{ $estadoClass }}" style="font-size:12px; padding:6px 10px;">
                                            {{ $estadoLabel }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="padding:14px 16px;">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar Fecha">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted" style="padding:20px;">
                                        No hay períodos configurados aún.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>

@endsection