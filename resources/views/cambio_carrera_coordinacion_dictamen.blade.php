<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dictamen Final - Cambio de Carrera</title>

    @vite(['resources/css/cambio_carrera.css', 'resources/js/cambio_carrera_coordinacion_dictamen.js'])
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

        <div class="topbar-center">Dictamen de Coordinación</div>
        <div class="topbar-right">
    <!-- BOTÓN ATRÁS -->
    <button onclick="window.history.back()" class="btn-back">
        ← Atrás
    </button>

    <!-- CERRAR SESIÓN -->
    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="btn-logout">
            Cerrar Sesión
        </button>
    </form>
</div>
    </header>

    <div class="page-wrap">
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

            <input type="hidden" id="id_tramite" value="{{ $id_tramite }}">
            <div id="msg" class="msg"></div>

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

            <div class="estado-resumen fade-in">
                <div class="estado-principal">
                    <h4>Documentos del trámite</h4>
                    <p>Coordinación también puede revisar los documentos cargados por el estudiante.</p>
                </div>

                <div class="estado-grid">
                    <div class="estado-item">
                        <strong>Historial académico</strong>
                        <span id="doc-historial">Cargando...</span>
                    </div>

                </div>
            </div>

            <hr>

            <div class="estado-resumen fade-in">
                <div class="estado-principal">
                    <h4>Dictamen final</h4>
                    <p>Seleccione la resolución final del trámite.</p>
                </div>

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
                <a class="btnLink" href="{{ route('cambio-carrera.coordinacion') }}">Volver al listado</a>
            </div>
        </div>
    </div>

</body>
</html>
