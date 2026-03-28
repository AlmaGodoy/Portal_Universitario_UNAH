@extends('layouts.app') {{-- Quitamos el rol manual, el Layout ya lo sabe por Auth --}}

@section('titulo', $titulo) {{-- Usamos el título que viene del Controller (ej. Gestión de Carrera) --}}

@section('content')
    {{-- 1. BREADCRUMB --}}
    <div style="padding:8px 18px 0;">
        <div class="breadcrumb-wrap">
            <i class="fas fa-home" style="color:var(--blue2);"></i>
            <a href="{{ route('empleado.dashboard') }}">Inicio</a>
            <span><i class="fas fa-chevron-right" style="font-size:.65rem;color:#bbb;"></i></span>
            <span>{{ $titulo }}</span>
        </div>
    </div>

    {{-- 2. CONTENIDO PRINCIPAL --}}
    <section class="content" style="padding:0 18px 80px;">

        {{-- BANNER DE LA FACULTAD (Este está perfecto, muy buena imagen la de la FCEAC) --}}
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

        {{-- BARRA DE BÚSQUEDA INTERNA --}}
        <div class="top-search-row">
            <div class="tsr-input-wrap">
                <input type="text" placeholder="Buscar trámite o estudiante..." id="top-search">
            </div>
            <div class="tsr-user">
                {{-- USAMOS LOS DATOS REALES DE LA BASE DE DATOS --}}
                <div class="tsr-avatar" id="top-initials">
                    {{ strtoupper(substr($userName, 0, 1)) }}{{ strtoupper(substr(explode(' ', $userName)[1] ?? '', 0, 1)) }}
                </div>
                <span class="tsr-name" id="top-username">{{ $userName }}</span>
            </div>
        </div>

        {{-- CAJAS DE INFORMACIÓN (ESTADÍSTICAS) --}}
        <div class="info-boxes-row">
            <div class="info-box-custom ibc-dark">
                <i class="fas fa-calendar-check ibc-icon-bg"></i>
                <div class="ibc-top">
                    <i class="fas fa-calendar-check ibc-icon-front"></i>
                    <div>
                        {{-- Aquí luego podés pasar una variable con el conteo real --}}
                        <div class="ibc-num">312</div>
                        <div class="ibc-label">Trámites Aprobados</div>
                    </div>
                </div>
                <div class="ibc-footer">{{ $userRole }}</div> {{-- Muestra si es Secretaría de Carrera o Académica --}}
            </div>
        </div>

    </section>
@endsection
