@extends('layouts.app-coordinador') 
{{-- CAMBIO: ahora usa el layout general del sistema --}}

@section('titulo', 'Dictamen Final - Cambio de Carrera')

@section('content')

{{-- ============================= --}}
{{-- HEADER DEL MÓDULO --}}
{{-- ============================= --}}
<div class="cc-header">
    <h2>Dictamen de Coordinación</h2>
    <p>Resolución final del trámite de cambio de carrera.</p>
</div>

{{-- ============================= --}}
{{-- CONTENIDO --}}
{{-- ============================= --}}
<div class="cc-container">

    <div class="card main-card">

        <div class="card-head">
            <div>
                <h3>Dictamen final del trámite</h3>
                <p>
                    Coordinación revisará la información validada por Secretaría y emitirá la resolución final.
                </p>
            </div>

            <span class="badge-soft">Trámite #{{ $id_tramite }}</span>
        </div>

        {{-- IMPORTANTE: NO TOCAR (JS depende de esto) --}}
        <input type="hidden" id="id_tramite" value="{{ $id_tramite }}">

        <div id="msg" class="msg"></div>

        {{-- ============================= --}}
        {{-- DATOS DEL TRÁMITE --}}
        {{-- ============================= --}}
        <div class="estado-resumen fade-in">
            <div class="estado-principal">
                <h4>Datos del trámite</h4>
                <p>Información general del cambio de carrera.</p>
            </div>

            <div class="estado-grid">

                <div class="estado-item">
                    <strong>ID Trámite</strong>
                    <span id="dato-id-tramite">Cargando...</span>
                </div>

                <div class="estado-item">
                    <strong>Fecha de solicitud</strong>
                    <span id="dato-fecha">Cargando...</span>
                </div>

                <div class="estado-item">
                    <strong>Nombre del estudiante</strong>
                    <span id="dato-estudiante">Cargando...</span>
                </div>

                <div class="estado-item">
                    <strong>Carrera destino</strong>
                    <span id="dato-carrera">Cargando...</span>
                </div>

                <div class="estado-item full">
                    <strong>Justificación</strong>
                    <span id="dato-justificacion">Cargando...</span>
                </div>

                <div class="estado-item">
                    <strong>Índice de período</strong>
                    <span id="dato-indice-periodo">Cargando...</span>
                </div>

                <div class="estado-item">
                    <strong>Índice global</strong>
                    <span id="dato-indice-global">Cargando...</span>
                </div>

                <div class="estado-item">
                    <strong>Clases aprobadas</strong>
                    <span id="dato-clases-aprobadas">Cargando...</span>
                </div>

            </div>
        </div>

        <hr>

        {{-- ============================= --}}
        {{-- DOCUMENTOS --}}
        {{-- ============================= --}}
        <div class="estado-resumen fade-in">
            <div class="estado-principal">
                <h4>Documentos del trámite</h4>
                <p>Coordinación puede revisar los documentos del estudiante.</p>
            </div>

            <div class="estado-grid">
                <div class="estado-item">
                    <strong>Historial académico</strong>
                    <span id="doc-historial">Cargando...</span>
                </div>
            </div>
        </div>

        <hr>

        {{-- ============================= --}}
        {{-- FORMULARIO DICTAMEN --}}
        {{-- ============================= --}}
        <div class="estado-resumen fade-in">
            <div class="estado-principal">
                <h4>Dictamen final</h4>
                <p>Seleccione la resolución final del trámite.</p>
            </div>

            {{-- IMPORTANTE: NO CAMBIAR IDs (JS depende de esto) --}}
            <form id="formDictamenCoordinacion">

                <label for="dictamen_final">Resolución final</label>
                <select id="dictamen_final" required>
                    <option value="">Seleccione...</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="rechazada">Rechazada</option>
                </select>

                <label for="observacion_dictamen">Observación del dictamen</label>
                <textarea
                    id="observacion_dictamen"
                    rows="4"
                    placeholder="Escriba aquí la observación final del dictamen"
                ></textarea>

                <button type="submit">Guardar dictamen final</button>
            </form>
        </div>

        <hr>

        <div class="registroBox">
            <a class="btnLink" href="{{ route('cambio-carrera.coordinacion') }}">
                Volver al listado
            </a>
        </div>

    </div>
</div>

{{-- JS ORIGINAL --}}
@vite('resources/js/cambio_carrera_coordinacion_dictamen.js')

@endsection