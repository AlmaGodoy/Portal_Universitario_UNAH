@extends('layouts.app-secretaria')

@section('titulo', 'Revisión de Secretaría - Cambio de Carrera')

@section('content')
   
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="cc-page">
        <div class="cc-header">
            <div class="cc-header-content">
                <div>
                    <h1>Revisión de Secretaría</h1>
                    <p>Secretaría revisará el historial académico del estudiante.</p>
                </div>

                <a href="javascript:history.back()" class="cc-btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="cc-card">
            <div class="cc-card-head">
                <div>
                    <h3>Revisión del trámite</h3>
                    <p>Secretaría revisará el historial académico.</p>
                </div>

                <span class="cc-badge">Trámite #{{ $id_tramite }}</span>
            </div>

            <input type="hidden" id="id_tramite" value="{{ $id_tramite }}">

            <div id="msg" class="msg"></div>

            <hr>

            <div class="estado-resumen fade-in">
                <div class="estado-principal">
                    <h4>Documentos del trámite</h4>
                    <p>Secretaría podrá revisar los archivos que subió el estudiante.</p>
                </div>

                <div class="estado-grid">
                    <div class="estado-item">
                        <strong>Historial académico</strong>
                        <span id="doc-historial">Cargando...</span>
                    </div>
                </div>
            </div>

            <hr>

            {{-- DATOS --}}
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
                        <strong>Motivo por el cuál solicita el cambio de carrera</strong>
                        <span id="dato-justificacion">Cargando...</span>
                    </div>

                    <div class="estado-item">
                        <strong>Estado del trámite</strong>
                        <span id="dato-estado-tramite">Cargando...</span>
                    </div>
                </div>
            </div>

            <hr>

            {{-- FORMULARIO --}}
            <div class="estado-resumen fade-in">
                <div class="estado-principal">
                    <h4>Validación de Secretaría</h4>
                    <p>Registrar datos académicos.</p>
                </div>

                <form id="formRevisionSecretaria">
                    <label for="indice_periodo">Índice de período</label>
                    <input type="number" step="0.01" id="indice_periodo" placeholder="Ej: 80.00">

                    <label for="indice_global">Índice global</label>
                    <input type="number" step="0.01" id="indice_global" placeholder="Ej: 82.00">

                    <label for="clases_aprobadas">Cantidad de clases aprobadas</label>
                    <input type="number" id="clases_aprobadas" placeholder="Ej: 10">

                    <label for="observaciones_secretaria">Observaciones de Secretaría</label>
                    <textarea
                        id="observaciones_secretaria"
                        rows="4"
                        placeholder="Escriba aquí observaciones sobre la revisión"
                    ></textarea>

                    <button type="submit" class="cc-btn-primary">
                        Guardar revisión de Secretaría
                    </button>
                </form>
            </div>

            <hr>

            <div class="registroBox">
                <a class="btnLink" href="{{ route('cambio-carrera.secretaria') }}">
                    Volver al listado
                </a>
            </div>
        </div>
    </div>
@endsection