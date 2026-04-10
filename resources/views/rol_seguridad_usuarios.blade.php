@php
    $layoutSeguridad = session('rol_texto') === 'secretaria_general'
        ? 'layouts.app-secretaria-academica'
        : 'layouts.app-coordinador';
@endphp

@extends($layoutSeguridad)

@section('titulo', 'Gestión de Usuarios')

@section('content')
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

    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="security-title">Gestión de Usuarios</h2>

            @if($esSecretariaGeneral ?? false)
                <p class="security-subtitle mb-0">
                    Administración de usuarios del sistema con filtros por carrera, rol, tipo y estado.
                </p>
            @else
                <p class="security-subtitle mb-0">
                    Administración de usuarios correspondientes a tu carrera.
                </p>
            @endif
        </div>

        <a href="{{ route('seguridad.index') }}" class="btn btn-outline-secondary">
            Volver a Seguridad
        </a>
    </div>

    <div class="card shadow border-0 security-card mb-4">
        <div class="card-header security-header">
            <span class="fw-bold text-white">Filtros de Búsqueda</span>
        </div>

        <div class="card-body bg-white">
            <form method="GET" action="{{ route('seguridad.usuarios') }}">
                @if($esSecretariaGeneral ?? false)

                    {{-- FILTROS SECRETARÍA GENERAL --}}
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label fw-bold">Buscar</label>
                            <input
                                type="text"
                                name="buscar"
                                class="form-control"
                                placeholder="Nombre o correo"
                                value="{{ $filtros['buscar'] ?? '' }}"
                            >
                        </div>

                        <div class="col-md-6 col-lg-2">
                            <label class="form-label fw-bold">Tipo</label>
                            <select name="tipo_usuario" class="form-select">
                                <option value="">Todos</option>
                                <option value="estudiante" {{ ($filtros['tipo_usuario'] ?? '') === 'estudiante' ? 'selected' : '' }}>Estudiante</option>
                                <option value="docente" {{ ($filtros['tipo_usuario'] ?? '') === 'docente' ? 'selected' : '' }}>Docente</option>
                                <option value="coordinador" {{ ($filtros['tipo_usuario'] ?? '') === 'coordinador' ? 'selected' : '' }}>Coordinador</option>
                                <option value="secretario" {{ ($filtros['tipo_usuario'] ?? '') === 'secretario' ? 'selected' : '' }}>Secretario</option>
                                <option value="secretaria" {{ ($filtros['tipo_usuario'] ?? '') === 'secretaria' ? 'selected' : '' }}>Secretaria</option>
                                <option value="secretaria_general" {{ ($filtros['tipo_usuario'] ?? '') === 'secretaria_general' ? 'selected' : '' }}>Secretaría General</option>
                                <option value="administrador" {{ ($filtros['tipo_usuario'] ?? '') === 'administrador' ? 'selected' : '' }}>Administrador</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-2">
                            <label class="form-label fw-bold">Rol</label>
                            <select name="id_rol" class="form-select">
                                <option value="">Todos</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id_rol }}" {{ (string)($filtros['id_rol'] ?? '') === (string)$rol->id_rol ? 'selected' : '' }}>
                                        {{ strtoupper($rol->nombre_rol) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-2">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="estado_cuenta" class="form-select">
                                <option value="">Todos</option>
                                <option value="1" {{ (string)($filtros['estado_cuenta'] ?? '') === '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ (string)($filtros['estado_cuenta'] ?? '') === '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <label class="form-label fw-bold">Carrera</label>
                            <select name="id_carrera" class="form-select">
                                <option value="">Todas</option>
                                @foreach($carreras as $carrera)
                                    <option value="{{ $carrera->id_carrera }}" {{ (string)($filtros['id_carrera'] ?? '') === (string)$carrera->id_carrera ? 'selected' : '' }}>
                                        {{ strtoupper($carrera->nombre_carrera) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary">
                                Filtrar
                            </button>

                            <a href="{{ route('seguridad.usuarios') }}" class="btn btn-outline-secondary">
                                Limpiar filtros
                            </a>
                        </div>
                    </div>

                @else

                    {{-- FILTROS COORDINADOR --}}
                    @php
                        $carreraActual = $carreras->firstWhere('id_carrera', $idCarreraActual);
                        $nombreCarreraActual = $carreraActual
                            ? strtoupper($carreraActual->nombre_carrera)
                            : 'CARRERA NO DEFINIDA';
                    @endphp

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Carrera</label>
                            <div
                                class="form-control input-highlight"
                                title="{{ $nombreCarreraActual }}"
                                style="min-height: 46px; height: auto; white-space: normal; line-height: 1.35; padding-top: 10px; padding-bottom: 10px;"
                            >
                                {{ $nombreCarreraActual }}
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <label class="form-label fw-bold">Buscar</label>
                            <input
                                type="text"
                                name="buscar"
                                class="form-control"
                                placeholder="Nombre o correo"
                                value="{{ $filtros['buscar'] ?? '' }}"
                            >
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <label class="form-label fw-bold">Tipo</label>
                            <select name="tipo_usuario" class="form-select">
                                <option value="">Todos</option>
                                <option value="estudiante" {{ ($filtros['tipo_usuario'] ?? '') === 'estudiante' ? 'selected' : '' }}>Estudiante</option>
                                <option value="coordinador" {{ ($filtros['tipo_usuario'] ?? '') === 'coordinador' ? 'selected' : '' }}>Coordinador</option>
                                <option value="secretario" {{ ($filtros['tipo_usuario'] ?? '') === 'secretario' ? 'selected' : '' }}>Secretario</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <label class="form-label fw-bold">Rol</label>
                            <select name="id_rol" class="form-select">
                                <option value="">Todos</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->valor }}" {{ (string)($filtros['id_rol'] ?? '') === (string)$rol->valor ? 'selected' : '' }}>
                                        {{ $rol->etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-2">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="estado_cuenta" class="form-select">
                                <option value="">Todos</option>
                                <option value="1" {{ (string)($filtros['estado_cuenta'] ?? '') === '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ (string)($filtros['estado_cuenta'] ?? '') === '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>

                        <div class="col-12 d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary">
                                Filtrar
                            </button>

                            <a href="{{ route('seguridad.usuarios') }}" class="btn btn-outline-secondary">
                                Limpiar filtros
                            </a>
                        </div>
                    </div>

                @endif
            </form>
        </div>
    </div>

    <div class="card shadow border-0 security-card">
        <div class="card-header security-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold text-white">Lista de Usuarios</span>

            <span class="badge bg-light text-dark">
                Total mostrados: {{ $usuarios->total() }}
            </span>
        </div>

        <div class="card-body bg-white">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Tipo Usuario</th>
                            <th>Rol</th>
                            <th>Carrera</th>
                            <th>Estado Cuenta</th>
                            <th class="col-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->id_usuario }}</td>
                                <td>{{ $usuario->nombre }}</td>
                                <td>{{ $usuario->correo }}</td>
                                <td>{{ $usuario->tipo_usuario }}</td>
                                <td>{{ $usuario->nombre_rol }}</td>
                                <td>{{ $usuario->nombre_carrera }}</td>
                                <td>
                                    @if((int)$usuario->estado_cuenta === 1)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm"
                                            data-toggle="modal"
                                            data-target="#modalEstadoUsuario{{ $usuario->id_usuario }}">
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    No hay usuarios registrados con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($usuarios, 'links'))
                <div class="mt-3">
                    {{ $usuarios->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

@foreach($usuarios as $usuario)
<div class="modal fade" id="modalEstadoUsuario{{ $usuario->id_usuario }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow">
            <form action="{{ route('seguridad.usuario.estado', $usuario->id_usuario) }}" method="POST" class="js-confirm-submit" data-confirm="¿Deseas guardar el cambio de estado de este usuario?">
                @csrf
                @method('PUT')

                <div class="modal-header security-header">
                    <h5 class="modal-title text-white">Actualizar Estado de Usuario</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre:</label>
                        <input type="text" class="form-control input-highlight" value="{{ $usuario->nombre }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Correo:</label>
                        <input type="text" class="form-control" value="{{ $usuario->correo }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Carrera:</label>
                        <input type="text" class="form-control" value="{{ $usuario->nombre_carrera }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado de cuenta:</label>
                        <select name="estado_cuenta" class="form-select" required>
                            <option value="1" {{ (int)$usuario->estado_cuenta === 1 ? 'selected' : '' }}>ACTIVO</option>
                            <option value="0" {{ (int)$usuario->estado_cuenta === 0 ? 'selected' : '' }}>INACTIVO</option>
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
@endsection
