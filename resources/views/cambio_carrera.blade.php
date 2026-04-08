@extends('layouts.app-estudiantes')

@section('titulo', 'Cambio de Carrera')

@section('content')
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="cc-page">
        <div class="cc-header">
            <div class="cc-header-content">
                <div>
                    <h1>Solicitud de Cambio de Carrera</h1>
                    <p>Completa tu trámite, adjunta tu historial académico y da seguimiento a tu solicitud.</p>
                </div>

            
                <a href="{{ route('dashboard') }}" class="cc-btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver al dashboard
                </a>
            </div>
        </div>

        <div class="cc-subnav-wrap">
            <nav class="cc-subnav">
                <a href="/cambio-carrera" class="active">Nuevo trámite</a>
                <a href="/cambio-carrera/mis-tramites">Mis trámites</a>
                <a href="/cambio-carrera/estado">Estado / Dictamen</a>
            </nav>
        </div>

        <div class="cc-card">
           
            <input type="hidden" id="id_persona" value="{{ session('persona_id') }}">
            <input type="hidden" id="id_calendario" value="">

            <div class="cc-card-head">
                <div>
                    <h3>Formulario de solicitud</h3>
                    <p>Registra tu solicitud para iniciar el proceso de cambio de carrera.</p>
                </div>
                <span class="cc-badge">Trámite estudiantil</span>
            </div>

            <p class="cc-info">
                Completa el formulario. Al crear el trámite, se habilitará la sección para subir tu
                <strong>Historial Académico (PDF)</strong>.
            </p>

            <form id="formCambioCarrera" class="cc-form">
                <div class="cc-form-group">
                    <label for="id_carrera_destino">Carrera destino</label>
                    <select id="id_carrera_destino" required>
                        <option value="">Cargando carreras...</option>
                    </select>
                </div>

                <div class="cc-form-group">
                    <label for="direccion">Motivo por el cual solicita el cambio de carrera</label>
                    <textarea
                        id="direccion"
                        placeholder="Escriba aquí el motivo por el cual solicita el cambio de carrera"
                        rows="4"
                        required
                    ></textarea>
                </div>

                <button type="submit" id="btnCrearTramite" class="cc-btn-primary">
                    Crear trámite
                </button>
            </form>

            <div id="msg" class="msg"></div>

       
            <div id="seccionHistorial" class="cc-historial" style="display:none;">
                <hr>

                <h3>Subir Historial Académico (PDF)</h3>

                <p class="cc-info">
                    Sube el PDF de tu historial académico. Este documento respalda las validaciones del trámite.
                </p>

  
                <form id="formHistorial" enctype="multipart/form-data" class="cc-form">
                    <input type="hidden" id="id_tramite" value="">

                    <div class="cc-form-group">
                        <label for="archivo">Selecciona tu PDF</label>
                        <input type="file" id="archivo" accept="application/pdf" required>
                    </div>

                   
                    <div id="previewArchivo" style="display:none;" class="cc-preview">
                        <p><strong>Archivo seleccionado:</strong> <span id="nombreArchivo"></span></p>
                        <p><strong>Tamaño:</strong> <span id="tamanoArchivo"></span></p>

                        <div class="cc-preview-actions">
                            <button type="button" id="btnVerArchivo" class="cc-btn-secondary">Ver PDF</button>
                            <button type="button" id="btnQuitarArchivo" class="cc-btn-danger">Quitar archivo</button>
                        </div>
                    </div>

                    <button type="submit" id="btnSubirPDF" class="cc-btn-primary">
                        Subir PDF
                    </button>
                </form>
            </div>

            <hr>

            <div class="cc-nota">
                <p>
                    <strong>Nota:</strong> Este sistema es de seguimiento. Para completar el proceso oficial,
                    también debes realizarlo en Registro UNAH.
                </p>
                <a class="cc-btn-link" href="https://registro.unah.edu.hn/" target="_blank" rel="noopener noreferrer">
                    Ir a Registro UNAH
                </a>
            </div>
        </div>
    </div>
@endsection