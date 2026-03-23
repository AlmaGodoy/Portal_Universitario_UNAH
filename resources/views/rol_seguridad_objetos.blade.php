<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Objetos</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/rolseguridad.css', 'resources/js/rolseguridad.js'])
</head>
<body>

<div class="container py-4 security-page">

    @if(session('status'))
        <div class="alert alert-success shadow-sm">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="security-title">Gestión de Objetos</h2>
            <p class="security-subtitle">Administración de módulos y submódulos de seguridad.</p>
        </div>

        <div>
            <a href="{{ route('seguridad.index') }}" class="btn btn-outline-secondary">
                Volver a Seguridad
            </a>

            <button class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#modalNuevoObjeto">
                + Nuevo Objeto
            </button>
        </div>
    </div>

    <div class="card shadow border-0 security-card">
        <div class="card-header security-header">
            <span class="fw-bold text-white">Lista de Objetos / Módulos</span>
        </div>

        <div class="card-body bg-white">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Objeto</th>
                            <th>Tipo de Objeto</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($objetos as $objeto)
                            <tr>
                                <td>{{ $objeto->id_objeto }}</td>
                                <td>{{ strtoupper($objeto->nombre_objeto) }}</td>
                                <td>{{ strtoupper($objeto->tipo_objeto) }}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarObjeto{{ $objeto->id_objeto }}">
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No hay objetos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="modalNuevoObjeto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <form action="{{ route('seguridad.objeto.store') }}" method="POST" class="js-confirm-submit" data-confirm="¿Deseas guardar este objeto?">
                @csrf

                <div class="modal-header security-header">
                    <h5 class="modal-title text-white">Nuevo Objeto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del objeto:</label>
                        <input type="text" name="nombre_objeto" class="form-control input-highlight" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de objeto:</label>
                        <select name="tipo_objeto" class="form-select" required>
                            <option value="">- SELECCIONE -</option>
                            <option value="MODULO">MODULO</option>
                            <option value="SUBMODULO">SUBMODULO</option>
                            <option value="PANTALLA">PANTALLA</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($objetos as $objeto)
<div class="modal fade" id="modalEditarObjeto{{ $objeto->id_objeto }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <form action="{{ route('seguridad.objeto.update', $objeto->id_objeto) }}" method="POST" class="js-confirm-submit" data-confirm="¿Deseas guardar los cambios de este objeto?">
                @csrf
                @method('PUT')

                <div class="modal-header security-header">
                    <h5 class="modal-title text-white">Editar Objeto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del objeto:</label>
                        <input type="text"
                               name="nombre_objeto"
                               class="form-control input-highlight"
                               value="{{ strtoupper($objeto->nombre_objeto) }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de objeto:</label>
                        <select name="tipo_objeto" class="form-select" required>
                            <option value="MODULO" {{ strtoupper($objeto->tipo_objeto) === 'MODULO' ? 'selected' : '' }}>MODULO</option>
                            <option value="SUBMODULO" {{ strtoupper($objeto->tipo_objeto) === 'SUBMODULO' ? 'selected' : '' }}>SUBMODULO</option>
                            <option value="PANTALLA" {{ strtoupper($objeto->tipo_objeto) === 'PANTALLA' ? 'selected' : '' }}>PANTALLA</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
