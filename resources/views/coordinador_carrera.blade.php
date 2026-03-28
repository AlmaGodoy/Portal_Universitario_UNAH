@extends('layouts.app')

@section('titulo', 'Panel de Coordinación')
@section('sidebar_role', 'Coordinación')

@section('sidebar_menu')
    <li class="nav-item">
        <a href="{{ route('empleado.dashboard') }}" class="nav-link {{ request()->is('empleado/dashboard*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-gauge-high"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ url()->current() }}" class="nav-link active">
            <i class="nav-icon fas fa-file-signature"></i>
            <p>Gestión de Dictámenes</p>
        </a>
    </li>

    {{-- TRÁMITES --}}
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-folder-open"></i>
            <p>Trámites <i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-ban"></i>
                    <p>Cancelación Excepcional</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-right-left"></i>
                    <p>Cambio de Carrera</p>
                </a>
            </li>
        </ul>
    </li>

    {{-- REPORTES --}}
    <li class="nav-item">
        <a href="{{ route('reporte.tramites.vista') }}" class="nav-link {{ request()->is('reporte-tramites-vista*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Reportes</p>
        </a>
    </li>

    {{-- SEGURIDAD --}}
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-shield-halved"></i>
            <p>Seguridad <i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('seguridad.index') }}" class="nav-link">
                    <i class="nav-icon fas fa-lock"></i>
                    <p>Panel General</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('seguridad.roles') }}" class="nav-link">
                    <i class="nav-icon fas fa-user-tag"></i>
                    <p>Roles</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('seguridad.usuarios') }}" class="nav-link">
                    <i class="nav-icon fas fa-users"></i>
                    <p>Usuarios</p>
                </a>
            </li>
        </ul>
    </li>

    {{-- RESPALDOS --}}
    <li class="nav-item">
        <a href="{{ route('backup.index') }}" class="nav-link {{ request()->is('respaldos*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-database"></i>
            <p>Respaldos</p>
        </a>
    </li>

    {{-- AUDITORÍA Y BITÁCORA (pendiente de enlace) --}}
    <li class="nav-item">
        <a href="#" class="nav-link text-muted" title="Próximamente">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <p>Auditoría <span class="badge badge-secondary ml-1">Pronto</span></p>
        </a>
    </li>

    <li class="nav-item">
        <a href="#" class="nav-link text-muted" title="Próximamente">
            <i class="nav-icon fas fa-book"></i>
            <p>Bitácora <span class="badge badge-secondary ml-1">Pronto</span></p>
        </a>
    </li>
@endsection

@section('content')
    @php
        $pendientesCancelacion   = $pendientesCancelacion   ?? 12;
        $pendientesCambioCarrera = $pendientesCambioCarrera ?? 8;

        $solicitudes = $solicitudes ?? [
            [
                'cuenta'     => '20211001234',
                'estudiante' => 'GADIEL GODOY',
                'tramite'    => 'Cancelación Excepcional',
                'fecha'      => '28/03/2026',
            ],
            [
                'cuenta'     => '20201005678',
                'estudiante' => 'VEGA PATRICIA',
                'tramite'    => 'Cambio de Carrera',
                'fecha'      => '27/03/2026',
            ],
        ];
    @endphp

    <div class="container-fluid" style="padding:15px 18px 80px;">

        <div style="padding:0 0 12px;">
            <h2 style="color: var(--blue-unah); font-weight: 800; margin-bottom: 6px;">
                Gestión de Dictámenes
            </h2>
            <p class="text-muted mb-0">Revisión de solicitudes de la Facultad (FCEAC)</p>
        </div>

        {{-- TARJETAS --}}
        <div class="row">
            <div class="col-md-6">
                <div style="background:#28a745; margin-bottom:20px; border-radius:15px; padding:20px; color:#fff; box-shadow:0 4px 12px rgba(0,0,0,.08);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-circle-check" style="font-size:32px; margin-right:15px;"></i>
                        <div>
                            <div style="font-size:30px; font-weight:800; line-height:1;">
                                {{ str_pad($pendientesCancelacion, 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <div style="font-size:15px; margin-top:6px;">Pendientes de Cancelación</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div style="background:#17a2b8; margin-bottom:20px; border-radius:15px; padding:20px; color:#fff; box-shadow:0 4px 12px rgba(0,0,0,.08);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-right-left" style="font-size:32px; margin-right:15px;"></i>
                        <div>
                            <div style="font-size:30px; font-weight:800; line-height:1;">
                                {{ str_pad($pendientesCambioCarrera, 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <div style="font-size:15px; margin-top:6px;">Pendientes Cambio de Carrera</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="card shadow-sm" style="border-radius:15px; border:none; overflow:hidden;">
            <div class="card-header" style="background: var(--blue-unah); color:white; border-radius:15px 15px 0 0;">
                <h3 class="card-title mb-0">
                    <i class="fas fa-list-check mr-2"></i> Solicitudes Entrantes
                </h3>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background:#f8f9fa;">
                            <tr>
                                <th style="padding:14px 16px;">Cuenta</th>
                                <th style="padding:14px 16px;">Estudiante</th>
                                <th style="padding:14px 16px;">Trámite</th>
                                <th style="padding:14px 16px;">Fecha</th>
                                <th class="text-center" style="padding:14px 16px; width:180px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($solicitudes as $solicitud)
                                @php
                                    $tramite      = $solicitud['tramite'] ?? 'Trámite';
                                    $tramiteLower = strtolower($tramite);
                                    $badgeClass   = str_contains($tramiteLower, 'cancel')
                                                    ? 'badge-danger'
                                                    : (str_contains($tramiteLower, 'cambio')
                                                        ? 'badge-info'
                                                        : 'badge-secondary');
                                @endphp
                                <tr>
                                    <td style="padding:14px 16px;">{{ $solicitud['cuenta'] ?? '' }}</td>
                                    <td style="padding:14px 16px;"><b>{{ $solicitud['estudiante'] ?? '' }}</b></td>
                                    <td style="padding:14px 16px;">
                                        <span class="badge {{ $badgeClass }}" style="font-size:13px; padding:8px 10px;">
                                            {{ $tramite }}
                                        </span>
                                    </td>
                                    <td style="padding:14px 16px;">{{ $solicitud['fecha'] ?? '' }}</td>
                                    <td class="text-center" style="padding:14px 16px;">
                                        <button type="button" class="btn btn-sm btn-outline-primary mr-1" title="Ver Documentos">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success mr-1" title="Aprobar Dictamen">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" title="Rechazar">
                                            <i class="fas fa-xmark"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted" style="padding:20px;">
                                        No hay solicitudes pendientes.
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
