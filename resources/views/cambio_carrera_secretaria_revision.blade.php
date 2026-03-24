<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión de Secretaría - Cambio de Carrera</title>

    @vite(['resources/css/cambio_carrera.css', 'resources/js/cambio_carrera_secretaria_revision.js'])
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

        <div class="topbar-center">Revisión de Secretaría</div>

        <div class="topbar-right">Cambio de Carrera</div>
    </header>

    <div class="page-wrap">
        <div class="card main-card">
            <div class="card-head">
                <div>
                    <h3>Revisión del trámite</h3>
                    <p>
                        Secretaría revisará el historial académico, el pago y la información académica del estudiante.
                    </p>
                </div>

                <span class="badge-soft">Trámite #{{ $id_tramite }}</span>
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

                    <div class="estado-item">
                        <strong>Comprobante de pago</strong>
                        <span id="doc-pago">Cargando...</span>
                    </div>
                </div>
            </div>

            <hr>

          
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
                        <strong>Estado del trámite</strong>
                        <span id="dato-estado-tramite">Cargando...</span>
                    </div>

                    <div class="estado-item">
                        <strong>Estado del pago</strong>
                        <span id="dato-estado-pago">Cargando...</span>
                    </div>
                </div>
            </div>

            <hr>

            <div class="estado-resumen fade-in">
                <div class="estado-principal">
                    <h4>Validación de Secretaría</h4>
                    <p>Registrar datos académicos y revisar el estado del pago.</p>
                </div>

                <form id="formRevisionSecretaria">
                    <label for="indice_periodo">Índice de período</label>
                    <input type="number" step="0.01" id="indice_periodo" placeholder="Ej: 80.00">

                    <label for="indice_global">Índice global</label>
                    <input type="number" step="0.01" id="indice_global" placeholder="Ej: 82.00">

                    <label for="clases_aprobadas">Cantidad de clases aprobadas</label>
                    <input type="number" id="clases_aprobadas" placeholder="Ej: 10">

                    
                    <label for="pago_validado">Estado del pago</label>
                    <select id="pago_validado">
                        <option value="">Seleccione...</option>
                        <option value="validado">Validado</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="rechazado">Rechazado</option>
                    </select>

                    
                    <label for="observaciones_secretaria">Observaciones de Secretaría</label>
                    <textarea
                        id="observaciones_secretaria"
                        rows="4"
                        placeholder="Escriba aquí observaciones sobre la revisión o sobre el pago"
                    ></textarea>

                    <button type="submit">Guardar revisión de Secretaría</button>
                </form>
            </div>

            <hr>

            <div class="registroBox">
                <a class="btnLink" href="{{ route('cambio-carrera.secretaria') }}">Volver al listado</a>
            </div>
        </div>
    </div>

</body>
</html>