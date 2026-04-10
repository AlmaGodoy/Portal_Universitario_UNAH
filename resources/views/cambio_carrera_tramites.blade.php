@extends('layouts.app-estudiantes')

@section('titulo', 'Mis Trámites - Cambio de Carrera')

@section('content')
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="cc-page">
        <div class="cc-header">
            <div class="cc-header-content">
                <div>
                    <h1>Mis Trámites de Cambio de Carrera</h1>
                    <p>Consulta el estado de tus solicitudes realizadas y revisa tu historial de trámites.</p>
                </div>

                <a href="{{ route('dashboard') }}" class="cc-btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver al dashboard
                </a>
            </div>
        </div>

        <div class="cc-subnav-wrap">
            <nav class="cc-subnav">
                <a href="/cambio-carrera">Nuevo trámite</a>
                <a href="/cambio-carrera/mis-tramites" class="active">Mis trámites</a>
                <a href="/cambio-carrera/estado">Estado / Dictamen</a>
            </nav>
        </div>

        <div class="cc-card">
          
            <input type="hidden" id="id_persona" value="{{ session('persona_id') }}">

            <div class="cc-card-head">
                <div>
                    <h3>Trámites realizados por el estudiante</h3>
                    <p>Aquí puedes ver todos los cambios de carrera que has solicitado.</p>
                </div>
                <span class="cc-badge">Historial</span>
            </div>
            <div id="msg" class="msg"></div>

           
            <div class="cc-table-wrap">
                <table id="tablaTramites" class="cc-table">
                    <thead>
                        <tr>
                            <th>ID Trámite</th>
                            <th>Fecha</th>
                            <th>Carrera Destino</th>
                            <th>Estado</th>
                            <th>Motivo por el cual solicita el cambio de carrera</th>
                            <th>Documento</th>

                            <th>Acciones</th>

                        </tr>
                    </thead>
                    <tbody id="tbodyTramites">
                        <tr>
                            <td colspan="7">Cargando trámites...</td>

                            <td colspan="6">Cargando trámites...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection