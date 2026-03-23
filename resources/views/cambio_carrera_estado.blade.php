<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado / Dictamen - Cambio de Carrera</title>

    @vite(['resources/css/cambio_carrera.css', 'resources/js/cambio_carrera_estado.js'])
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
        <div class="topbar-right">Portal Académico</div>
    </header>

    <main class="module-container">
        <section class="module-header">
            <h2>Estado / Dictamen</h2>
            <p>Da seguimiento visual a tu solicitud de cambio de carrera.</p>
        </section>

        <nav class="subnav">
            <a href="/cambio-carrera">Nuevo trámite</a>
            <a href="/cambio-carrera/mis-tramites">Mis trámites</a>
            <a href="/cambio-carrera/estado" class="active">Estado / Dictamen</a>
        </nav>

        <div class="card main-card">
          <input type="hidden" id="id_persona" value="{{ session('persona_id') }}">

            <div class="card-head">
                <div>
                    <h3>Seguimiento del trámite</h3>
                    <p>Aquí puedes consultar el estado actual y la resolución de tu solicitud.</p>
                </div>
                <span class="badge-soft">Seguimiento</span>
            </div>

            <div id="estadoTramite">
                <p class="info">Cargando estado del trámite...</p>
            </div>
        </div>
    </main>

</body>
</html>