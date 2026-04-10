@php
    $layoutSeguridad = session('rol_texto') === 'secretaria_general'
        ? 'layouts.app-secretaria-academica'
        : 'layouts.app-coordinador';
@endphp

@extends($layoutSeguridad)

@section('titulo', 'Gestión de Roles')

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

    <div class="row g-4">

        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h2 class="security-title">Gestión de Roles</h2>
                    <p class="security-subtitle">Administración global de roles del sistema. Exclusivo para Secretaría General.</p>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('seguridad.index') }}" class="btn btn-outline-secondary">
                        Volver a Seguridad
                    </a>

                    <button class="btn btn-success" data-toggle="modal" data-target="#modalNuevoRol">
                        + Nuevo Rol
                    </button>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow border-0 security-card">
                <div class="card-header security-header">
                    <span class="fw-bold text-white">Filtros de Roles</span>
                </div>

                <div class="card-body bg-white">
                    <form method="GET" action="{{ route('seguridad.roles') }}">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-5">
                                <label class="form-label fw-bold">Buscar por nombre o descripción</label>
                                <input
                                    type="text"
                                    name="buscar"
                                    class="form-control"
                                    placeholder="Ej. estudiante, coordinador..."
                                    value="{{ $filtros['buscar'] ?? '' }}"
                                >
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-bold">Estado</label>
                                <select name="estado_activo" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="1" {{ (string)($filtros['estado_activo'] ?? '') === '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ (string)($filtros['estado_activo'] ?? '') === '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>

                            <div class="col-12 d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn btn-primary">
                                    Filtrar
                                </button>

                                <a href="{{ route('seguridad.roles') }}" class="btn btn-outline-secondary">
                                    Limpiar filtros
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow border-0 security-card">
                <div class="card-header security-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-bold text-white">Lista de Roles</span>

                    <span class="badge bg-light text-dark">
                        Total roles: {{ count($roles) }}
                    </span>
                </div>

                <div class="card-body bg-white">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Rol</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th class="col-acciones">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $rol)
                                    <tr>
                                        <td>{{ $rol->id_rol }}</td>
                                        <td>{{ strtoupper($rol->nombre_rol) }}</td>
                                        <td>{{ $rol->descripcion }}</td>
                                        <td>
                                            @if((int)$rol->estado_activo === 1)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm"
                                                    data-toggle="modal"
                                                    data-target="#modalEditarRol{{ $rol->id_rol }}">
                                                Editar
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No hay roles registrados con los filtros seleccionados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow border-0 security-card">
                <div class="card-header security-header">
                    <span class="fw-bold text-white">Asignar Permisos por Objeto</span>
                </div>

                <div class="card-body bg-white">
                    <form action="{{ route('seguridad.asignar.objeto') }}" method="POST" class="js-confirm-submit" data-confirm="¿Deseas guardar esta asignación de permisos?">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label fw-bold">Rol</label>
                                <select name="id_rol" class="form-select" required>
                                    <option value="">- SELECCIONE ROL -</option>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->id_rol }}">
                                            {{ strtoupper($rol->nombre_rol) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label fw-bold">Objeto</label>
                                <select name="id_objeto" class="form-select" required>
                                    <option value="">- SELECCIONE OBJETO -</option>
                                    @foreach($objetos as $objeto)
                                        <option value="{{ $objeto->id_objeto }}">
                                            {{ strtoupper($objeto->nombre_objeto) }} ({{ strtoupper($objeto->tipo_objeto) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold d-block mb-2">Permisos</label>

                                <div class="row">
                                    @foreach($permisos as $permiso)
                                        <div class="col-md-6 col-lg-3 mb-2">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="permisos[]"
                                                    value="{{ $permiso->id_permiso }}"
                                                    id="permiso{{ $permiso->id_permiso }}"
                                                >
                                                <label class="form-check-label" for="permiso{{ $permiso->id_permiso }}">
                                                    {{ strtoupper($permiso->nombre_permiso) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    Guardar Asignación
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow border-0 security-card">
                <div class="card-header security-header">
                    <span class="fw-bold text-white">Asignaciones de Roles</span>
                </div>

                <div class="card-body bg-white">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Rol</th>
                                    <th>Objeto</th>
                                    <th>Permiso</th>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rolPermisos as $rp)
                                    <tr>
                                        <td>{{ $rp->id_rol_permiso }}</td>
                                        <td>{{ strtoupper($rp->nombre_rol) }}</td>
                                        <td>{{ strtoupper($rp->nombre_objeto) }}</td>
                                        <td>{{ strtoupper($rp->nombre_permiso) }}</td>
                                        <td>{{ $rp->fecha_asignacion }}</td>
                                        <td>
                                            <form action="{{ route('seguridad.asignacion.delete', $rp->id_rol_permiso) }}"
                                                  method="POST"
                                                  class="js-confirm-delete"
                                                  data-confirm="¿Desactivar esta asignación?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-warning btn-sm text-dark">
                                                    Desactivar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No hay asignaciones registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="modalNuevoRol" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow">
            <form action="{{ route('seguridad.rol.store') }}" method="POST" class="js-confirm-submit" data-confirm="¿Deseas guardar este rol?">
                @csrf

                <div class="modal-header security-header">
                    <h5 class="modal-title text-white">Nuevo Rol</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rol:</label>
                        <input type="text" name="nombre_rol" class="form-control input-highlight" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción:</label>
                        <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado del Rol:</label>
                        <select name="estado_activo" class="form-select" required>
                            <option value="1">ACTIVO</option>
                            <option value="0">INACTIVO</option>
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

@foreach($roles as $rol)
<div class="modal fade" id="modalEditarRol{{ $rol->id_rol }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow">
            <form action="{{ route('seguridad.rol.update', $rol->id_rol) }}" method="POST" class="js-confirm-submit" data-confirm="¿Deseas guardar los cambios de este rol?">
                @csrf
                @method('PUT')

                <div class="modal-header security-header">
                    <h5 class="modal-title text-white">Editar Rol</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rol:</label>
                        <input type="text"
                               name="nombre_rol"
                               class="form-control input-highlight"
                               value="{{ strtoupper($rol->nombre_rol) }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción:</label>
                        <textarea name="descripcion" class="form-control" rows="3" required>{{ $rol->descripcion }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado del Rol:</label>
                        <select name="estado_activo" class="form-select" required>
                            <option value="1" {{ (int)$rol->estado_activo === 1 ? 'selected' : '' }}>ACTIVO</option>
                            <option value="0" {{ (int)$rol->estado_activo === 0 ? 'selected' : '' }}>INACTIVO</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
