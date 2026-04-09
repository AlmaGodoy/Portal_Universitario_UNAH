@extends('layouts.app-coordinador')

@section('titulo', 'Panel de Coordinación')

@section('content')

    {{-- BANNER PRINCIPAL --}}
    <div class="hero-banner">
        <div class="hero-banner-bg"></div>
        <div class="hero-wave wave-one"></div>
        <div class="hero-wave wave-two"></div>
        <div class="hero-gold-ribbon"></div>

        <div class="hero-photo">
            <img src="{{ asset('images/FCEAC.jpg') }}" alt="Edificio FCEAC" class="hero-photo-img">
        </div>

        <div class="hero-content">
            <div class="hero-top-title">Portal de Coordinación UNAH</div>

            <div class="hero-breadcrumb">
                <i class="fas fa-house"></i>
                <span>Inicio</span>
                <i class="fas fa-angle-right sep"></i>
                <span>Panel de Coordinación</span>
            </div>

            <div class="hero-faculty-title">
                FACULTAD DE CIENCIAS ECONÓMICAS,<br>
                ADMINISTRATIVAS Y CONTABLES
            </div>
        </div>
    </div>

    {{-- FRANJA INFORMATIVA --}}
    <div class="student-intro-strip">
        <div class="student-intro-text">
            <h2>Panel de coordinación</h2>
            <p>Gestiona solicitudes académicas, revisa trámites pendientes y da seguimiento a los dictámenes asignados.</p>
        </div>

        <div class="student-user-chip">
            @php
                $nombreUsuario = $userName ?? auth()->user()->name ?? 'Coordinador';
                $partesNombre = preg_split('/\s+/', trim($nombreUsuario));
                $iniciales = '';

                foreach (array_slice($partesNombre, 0, 2) as $parte) {
                    if (!empty($parte)) {
                        $iniciales .= strtoupper(mb_substr($parte, 0, 1));
                    }
                }

                if ($iniciales === '') {
                    $iniciales = 'C';
                }
            @endphp

            <div class="student-user-chip-avatar">{{ $iniciales }}</div>
            <div class="student-user-chip-name">{{ $nombreUsuario }}</div>
        </div>
    </div>

    <section class="content" style="padding: 0 0 80px;">

        {{-- ESTADÍSTICAS --}}
        @php
            $pendientesCancelacion   = $pendientesCancelacion ?? 12;
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

        <div class="student-info-grid" style="margin-top: 24px;">
            <div class="info-panel">
                <div class="info-panel-header">
                    <i class="fas fa-file-circle-check"></i>
                    <h3>Pendientes de cancelación</h3>
                </div>

                <div class="info-panel-body">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                        <div>
                            <div style="font-size: 2rem; font-weight: 800; color: #003c71; line-height:1;">
                                {{ str_pad($pendientesCancelacion, 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <p style="margin:8px 0 0; color:#5b6475;">
                                Solicitudes en espera de revisión por coordinación.
                            </p>
                        </div>
                        <div class="module-icon" style="width:64px; height:64px;">
                            <i class="fas fa-file-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-panel">
                <div class="info-panel-header">
                    <i class="fas fa-right-left"></i>
                    <h3>Pendientes cambio de carrera</h3>
                </div>

                <div class="info-panel-body">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                        <div>
                            <div style="font-size: 2rem; font-weight: 800; color: #003c71; line-height:1;">
                                {{ str_pad($pendientesCambioCarrera, 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <p style="margin:8px 0 0; color:#5b6475;">
                                Trámites de cambio de carrera pendientes de dictamen.
                            </p>
                        </div>
                        <div class="module-icon gold" style="width:64px; height:64px;">
                            <i class="fas fa-right-left"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN DE GESTIÓN --}}
        <div class="student-section-title" style="margin-top: 28px;">
            <h3>Gestión de dictámenes</h3>
            <p>Consulta los trámites asignados y realiza las acciones correspondientes desde este panel.</p>
        </div>

        {{-- TABLA DE SOLICITUDES --}}
        <div class="card shadow-sm" style="border-radius: 18px; border: none; overflow: hidden; margin-top: 12px;">
            <div class="card-header" style="background: var(--blue-unah, #003c71); color: white; border: none; padding: 16px 20px;">
                <h3 class="card-title mb-0" style="font-size: 1.05rem; font-weight: 700;">
                    <i class="fas fa-list-check mr-2"></i> Solicitudes pendientes
                </h3>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 14px 16px;">Cuenta</th>
                                <th style="padding: 14px 16px;">Estudiante</th>
                                <th style="padding: 14px 16px;">Trámite</th>
                                <th style="padding: 14px 16px;">Fecha</th>
                                <th class="text-center" style="padding: 14px 16px; width: 180px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($solicitudes as $solicitud)
                                @php
                                    $tramite = $solicitud['tramite'] ?? 'Trámite';
                                    $tramiteLower = strtolower($tramite);
                                    $badgeClass = str_contains($tramiteLower, 'cancel')
                                        ? 'badge-danger'
                                        : (str_contains($tramiteLower, 'cambio') ? 'badge-info' : 'badge-secondary');
                                @endphp
                                <tr>
                                    <td style="padding: 14px 16px;">{{ $solicitud['cuenta'] ?? '' }}</td>
                                    <td style="padding: 14px 16px;"><strong>{{ $solicitud['estudiante'] ?? '' }}</strong></td>
                                    <td style="padding: 14px 16px;">
                                        <span class="badge {{ $badgeClass }}" style="font-size: 13px; padding: 8px 10px;">
                                            {{ $tramite }}
                                        </span>
                                    </td>
                                    <td style="padding: 14px 16px;">{{ $solicitud['fecha'] ?? '' }}</td>
                                    <td class="text-center" style="padding: 14px 16px;">
                                        <button type="button" class="btn btn-sm btn-outline-primary mr-1" title="Ver documentos">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success mr-1" title="Aprobar dictamen">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" title="Rechazar">
                                            <i class="fas fa-xmark"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted" style="padding: 20px;">
                                        No hay solicitudes pendientes.
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