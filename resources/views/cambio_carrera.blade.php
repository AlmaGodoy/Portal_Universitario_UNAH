<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Carrera</title>

    @vite(['resources/css/cambio_carrera.css', 'resources/js/cambio_carrera.js'])
</head>
<body>

    <header class="topbar">
        <div class="brand">
            <img src="{{ asset('images/abejita.jpeg') }}" alt="Logo PumaGestión" class="brand-logo">

            <div class="brand-text">
                <h1 class="brand-title">
                    <span class="puma">Puma</span><span class="gestion">Gestión</span>
                </h1>
                <span class="brand-subtitle">FCEAC - UNAH</span>
            </div>
        </div>

        <div class="topbar-center">Cambio de Carrera</div>
<div class="topbar-right">
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-logout">Cerrar Sesión</button>
            </form>
        </div>
    </header>

    <div class="page-wrap">
        <nav class="subnav">
            <a href="javascript:history.back()" class="btn-back">← Atrás</a>>
        <a href="/cambio-carrera" class="active">Nuevo trámite</a>
        <a href="/cambio-carrera/mis-tramites">Mis trámites</a>
        <a href="/cambio-carrera/estado">Estado / Dictamen</a>
    </nav>

        <div class="card">
            <h2>Solicitud de Cambio de Carrera</h2>

            <p class="info">
                Completa el formulario. Al crear el trámite, se habilitará la sección para subir tu <b>Historial Académico (PDF)</b>.
            </p>

            <form id="formCambioCarrera">
                <input type="hidden" id="id_persona" value="{{ session('persona_id') }}">
                <input type="hidden" id="id_calendario" value="">

                <label for="id_carrera_destino">Carrera destino</label>
                <select id="id_carrera_destino" required>
                    <option value="">Cargando carreras...</option>
                </select>

                <label for="direccion">Motivo por el cual solicita el cambio de carrera</label>
              <textarea
                    id="direccion"
                    placeholder="Escriba aquí el motivo por la cual solicita el cambio de carrera"
                    rows="4"
                    required
                ></textarea>

                <button type="submit" id="btnCrearTramite">Crear trámite</button>
            </form>

            <hr>

            <div id="seccionHistorial" style="display:none;">
                <h3>Subir Historial Académico (PDF)</h3>

                <p class="info">
                    Sube el PDF de tu historial académico. Este documento respalda las validaciones del trámite.
                </p>

                <form id="formHistorial" enctype="multipart/form-data">
                    <input type="hidden" id="id_tramite" value="">

                    <label for="archivo">Selecciona tu PDF</label>
                    <input type="file" id="archivo" accept="application/pdf" required>

                    <div id="previewArchivo" style="display:none;" class="preview-archivo">
                    <p><strong>Archivo seleccionado:</strong> <span id="nombreArchivo"></span></p>
                    <p><strong>Tamaño:</strong> <span id="tamanoArchivo"></span></p>

                    <div class="preview-actions">
                     <button type="button" id="btnVerArchivo">Ver PDF</button>
                     <button type="button" id="btnQuitarArchivo">Quitar archivo</button>
    </div>
</div>

<button type="submit" id="btnSubirPDF">Subir PDF</button>

                </form>
            </div>

            <div id="msg" class="msg"></div>

            <hr>



            <div class="registroBox">
                <p><b>Nota:</b> Este sistema es de seguimiento. Para completar el proceso oficial, también debes realizarlo en Registro UNAH.</p>
                <a class="btnLink" href="https://registro.unah.edu.hn/" target="_blank">Ir a Registro UNAH</a>
            </div>

    </div>

</body>
</html>