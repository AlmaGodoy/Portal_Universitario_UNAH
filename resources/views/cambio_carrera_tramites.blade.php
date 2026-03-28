<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Trámites - Cambio de Carrera</title>

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

    <main class="module-container">
        <section class="module-header">
            <h2>Mis Trámites de Cambio de Carrera</h2>
            <p>Consulta el estado de tus solicitudes realizadas.</p>
        </section>


    <div class="page-wrap">
        <nav class="subnav">
            <a href="javascript:history.back()" class="btn-back">← Atrás</a>>
            <a href="/cambio-carrera">Nuevo trámite</a>
            <a href="/cambio-carrera/mis-tramites" class="active">Mis trámites</a>
            <a href="/cambio-carrera/estado">Estado / Dictamen</a>
        </nav>

        <div class="card main-card">
            <input type="hidden" id="id_persona" value="{{ session('persona_id') }}">

            <div class="card-head">
                <div>
                    <h3>Trámites realizados por el estudiante</h3>
                    <p>Aquí puedes ver todos los cambios de carrera que has solicitado.</p>
                </div>
                <span class="badge-soft">Historial</span>
            </div>

            <table id="tablaTramites">
                <thead>
                    <tr>
                        <th>ID Trámite</th>
                        <th>Fecha</th>
                        <th>Carrera Destino</th>
                        <th>Estado</th>
                        <th>Motivo por el cuál solicita el cambio de carrera</th>
                         <th>Documento</th>
                    </tr>
                </thead>
                <tbody id="tbodyTramites">
                    <tr>
                        <td colspan="6">Cargando trámites...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>