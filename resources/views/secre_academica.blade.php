@extends('layouts.app')

@section('titulo', 'Control Académico - FCEAC')

@section('content')
    {{-- BREADCRUMB GLOBAL --}}
    <div style="padding:8px 18px 0;">
        <div class="breadcrumb-wrap">
            <i class="fas fa-university" style="color:var(--blue-unah);"></i>
            <a href="{{ route('empleado.dashboard') }}">Inicio</a>
            <span><i class="fas fa-chevron-right" style="font-size:.65rem;color:#bbb;"></i></span>
            <span>Gestión Académica Facultad</span>
        </div>
    </div>

    {{-- CONTENIDO PRINCIPAL --}}
    <section class="content" style="padding:0 18px 80px;">

        {{-- BANNER GLOBAL (FCEAC) --}}
        <div class="faculty-banner" style="background: linear-gradient(90deg, #002d57 0%, #004a8f 100%);">
            <div class="fb-bg"></div>
            <div class="fb-photo-right" style="background-image: url('{{ asset('images/FCEAC.jpeg') }}');"></div>
            <div class="fb-content">
                <div>
                    <div class="fb-title-main">
                        Secretaría General Académica<br>Control Facultad (FCEAC)
                    </div>
                    <div class="fb-subtitle">Nivel Global · UNAH · PumaGestión</div>
                </div>
            </div>
        </div>

        {{-- BARRA DE BÚSQUEDA GLOBAL --}}
        <div class="top-search-row">
            <div class="tsr-input-wrap">
                <input type="text" placeholder="Buscar expediente en toda la facultad..." id="top-search">
            </div>
            <div class="tsr-user">
                <div class="tsr-avatar" style="background: var(--blue-unah); color: white;">
                    {{ strtoupper(substr($userName, 0, 1)) }}{{ strtoupper(substr(explode(' ', $userName)[1] ?? '', 0, 1)) }}
                </div>
                <span class="tsr-name">{{ $userName }}</span>
            </div>
        </div>

        <div class="info-boxes-row">
            <div class="info-box-custom ibc-dark" style="width: 100%; max-width: 400px; background: #2c3e50;">
                <i class="fas fa-chart-line ibc-icon-bg"></i>
                <div class="ibc-top">
                    <i class="fas fa-sync-alt fa-spin ibc-icon-front" style="color: #ffd700;"></i>
                    <div>
                        <div class="ibc-num">Pendiente</div>
                        <div class="ibc-label">Estadísticas Globales de Facultad</div>
                    </div>
                </div>
                <div class="ibc-footer">Módulo de analítica en desarrollo</div>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 15px; border-radius: 8px; background: #e8f4fd; color: #003b71; border-left: 5px solid #003b71;">
            <i class="fas fa-info-circle"></i> <strong>Nota de Proyecto:</strong> Esta vista está configurada para la <b>Secretaría Académica</b>. Los datos mostrados aquí ignoran el filtro de carrera para permitir supervisión total.
        </div>

    </section>
@endsection
