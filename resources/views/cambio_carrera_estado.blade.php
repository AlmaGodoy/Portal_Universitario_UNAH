@extends('layouts.app-estudiantes')

@section('titulo', 'Estado / Dictamen - Cambio de Carrera')

@section('content')
   

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="cc-page">
        <div class="cc-header">
            <div class="cc-header-content">
                <div>
                    <h1>Estado / Dictamen</h1>
                    <p>Da seguimiento visual a tu solicitud de cambio de carrera.</p>
                </div>

                <a href="{{ route('dashboard') }}" class="cc-btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver al dashboard
                </a>
            </div>
        </div>

        <div class="cc-subnav-wrap">
            <nav class="cc-subnav">
                <a href="/cambio-carrera">Nuevo trámite</a>
                <a href="/cambio-carrera/mis-tramites">Mis trámites</a>
                <a href="/cambio-carrera/estado" class="active">Estado / Dictamen</a>
            </nav>
        </div>

        <div class="cc-card">
          
            <input type="hidden" id="id_persona" value="{{ session('persona_id') }}">

            <div class="cc-card-head">
                <div>
                    <h3>Seguimiento del trámite</h3>
                    <p>Aquí puedes consultar el estado actual y la resolución de tu solicitud.</p>
                </div>
                <span class="cc-badge">Seguimiento</span>
            </div>

            {{-- IMPORTANTE: se conserva este id porque el JS lo usa --}}
            <div id="estadoTramite">
                <p class="cc-info">Cargando estado del trámite...</p>
            </div>
        </div>
    </div>
@endsection