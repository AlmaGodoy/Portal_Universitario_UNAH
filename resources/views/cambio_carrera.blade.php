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

        <div class="topbar-right">Login</div>
    </header>

    <div class="page-wrap">
        <nav class="subnav">
        <a href="/cambio-carrera" class="active">Nuevo trámite</a>
        <a href="/cambio-carrera/mis-tramites">Mis trámites</a>
        <a href="/cambio-carrera/estado">Estado / Dictamen</a>
    </nav>

        <div class="card">
            <h2>Solicitud de Cambio de Carrera</h2>

            <p class="info">
                Completa el formulario. Al crear el trámite, se habilitarán las secciones para subir tu <b>Historial Académico (PDF)</b> y registrar el <b>pago del trámite</b>.
            </p>

            <form id="formCambioCarrera">
                <input type="hidden" id="id_persona" value="{{ session('persona_id') }}">
                <input type="hidden" id="id_calendario" value="">

                <label for="id_carrera_destino">Carrera destino</label>
                <select id="id_carrera_destino" required>
                    <option value="">Cargando carreras...</option>
                </select>

                <label for="direccion">Justificación por la cual solicita el cambio de carrera</label>
              <textarea
                    id="direccion"
                    placeholder="Escriba aquí la justificación por la cual solicita el cambio de carrera"
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

             <hr>



            <div id="msg" class="msg"></div>

            <hr>
               <div id="seccionPago" style="display:none;">
                <h3>Registrar pago del trámite</h3>

                 <p class="info">
                    Este trámite requiere un pago de <b>L 200.00</b>. Adjunta tu comprobante de pago para que sea validado por secretaría o coordinación.
                </p>

                <form id="formPago" enctype="multipart/form-data">
                     <input type="hidden" id="id_tramite_pago" value="">

                    <label for="fecha_pago">Fecha de pago</label>
                     <input type="date" id="fecha_pago" required>

                    <label for="id_banco">Banco (opcional)</label>
                    <select id="id_banco">
                        <option value="">Seleccione un banco</option>
                    </select>

                    <label for="referencia_banco">Referencia bancaria (opcional)</label>
                     <input type="text" id="referencia_banco" placeholder="Ingrese la referencia si la conoce">

                    <label for="observaciones_pago">Observaciones (opcional)</label>
                    <textarea
                        id="observaciones_pago"
                        rows="3"
                        placeholder="Escriba aquí alguna observación sobre el pago"
                    ></textarea>

                    <label for="comprobante_pago">Comprobante de pago</label>
                     <input type="file" id="comprobante_pago" accept=".pdf,.jpg,.jpeg,.png" required>
                      <div id="previewComprobante" style="display:none;" class="preview-archivo">
                        <p><strong>Archivo seleccionado:</strong> <span id="nombreComprobante"></span></p>
                        <p><strong>Tamaño:</strong> <span id="tamanoComprobante"></span></p>

                        <div class="preview-actions">
                            <button type="button" id="btnVerComprobante">Ver archivo</button>
                            <button type="button" id="btnQuitarComprobante">Quitar archivo</button>
                        </div>
                    </div>

                    <button type="submit" id="btnRegistrarPago">Registrar pago</button>
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