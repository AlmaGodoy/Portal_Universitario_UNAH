<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Seguridad</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/rolseguridad.css', 'resources/js/rolseguridad.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<div class="container py-4 security-page fondo-seguridad">

    <div class="mb-4">
        <h2 class="security-title">Módulo de Seguridad</h2>
        <p class="security-subtitle">Administración de roles, usuarios, objetos y accesos del sistema.</p>
    </div>

    <div class="row g-4">

        @foreach($modulos as $modulo)

        <div class="col-md-6 col-lg-3">
            <div class="card shadow border-0 h-100 security-card">
                <div class="card-body text-center d-flex flex-column">

                    <div class="mb-3">
                        <i class="bi {{ $modulo['icono'] }} fs-1 text-primary"></i>
                    </div>

                    <h5 class="fw-bold">{{ $modulo['titulo'] }}</h5>

                    <p class="text-muted small">
                        {{ $modulo['descripcion'] }}
                    </p>

                    <a href="{{ $modulo['ruta'] }}" class="btn btn-primary w-100 mt-auto">
                        Ingresar
                    </a>

                </div>
            </div>
        </div>

        @endforeach

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
