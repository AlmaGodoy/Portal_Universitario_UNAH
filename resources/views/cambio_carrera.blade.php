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

<div class="card">
    <h2>Solicitud de Cambio de Carrera</h2>

    <p class="info">
        Completa el formulario. Al crear el trámite, se habilitará la sección para subir tu <b>Historial Académico (PDF)</b>.
    </p>

    <form id="formCambioCarrera">
        <!-- (TEMPORAL) mientras no hay login -->
        <input type="hidden" id="id_persona" value="20">
        <input type="hidden" id="id_calendario" value="">

        <label for="id_carrera_destino">Carrera destino</label>
        <select id="id_carrera_destino" required>
            <option value="">Cargando carreras...</option>
        </select>

        <label for="direccion">Justificación por la cual solicita el cambio de carrera</label>
        <input type="text" id="direccion" placeholder="justificación" required>

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