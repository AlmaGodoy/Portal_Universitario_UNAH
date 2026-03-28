<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secretaría - Cambio de Carrera</title>

    @vite(['resources/css/cambio_carrera.css', 'resources/js/cambio_carrera_secretaria.js'])
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

        <div class="topbar-center">Secretaría - Cambio de Carrera</div>

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
                    <h3>Trámites pendientes de revisión</h3>
                    <p>
                        Aquí Secretaría podrá revisar el historial académico.
                    </p>
                </div>

                <span class="badge-soft">Secretaría</span>
            </div>

            <div id="msg" class="msg"></div>

            <table>
                <thead>
                    <tr>
                        <th>ID Trámite</th>
                        <th>Fecha</th>
                        <th>Nombre del Estudiante</th>
                        <th>Carrera Destino</th>
                        <th>Estado Trámite</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="tbodySecretaria">
                    <tr>
                        <td colspan="7">Cargando trámites...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>