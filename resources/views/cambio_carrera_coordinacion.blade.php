<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinación - Cambio de Carrera</title>

    @vite(['resources/css/cambio_carrera.css', 'resources/js/cambio_carrera_coordinacion.js'])
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

        <div class="topbar-center">Coordinación - Cambio de Carrera</div>

        <div class="topbar-right">
            <!-- BOTÓN ATRÁS -->
            <button onclick="window.history.back()" class="btn-back">
                ← Atrás
            </button>

            <!-- BOTÓN CERRAR SESIÓN -->
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-logout">
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </header>
    </header>

    <div class="page-wrap">
        <div class="card main-card">
            <div class="card-head">
                <div>
                    <h3>Trámites pendientes de dictamen</h3>
                    <p>
                        Aquí Coordinación revisará la información validada por Secretaría y emitirá el dictamen final.
                    </p>
                </div>

                <span class="badge-soft">Coordinación</span>
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
                <tbody id="tbodyCoordinacion">
                    <tr>
                        <td colspan="7">Cargando trámites...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
