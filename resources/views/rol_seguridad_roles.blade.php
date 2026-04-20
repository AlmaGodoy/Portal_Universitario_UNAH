@extends('layouts.app-coordinador')
@section('hide_topbar', true)
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
                    <p class="security-subtitle">
                        Administración de roles correspondientes únicamente a tu carrera.
                    </p>
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
                    <span class="fw-bold text-white">Lista de Roles por Carrera</span>

                    <span class="badge bg-light text-dark">
                        Total roles: {{ count($roles) }}
                    </span>
                </div>

                <div class="card-body bg-white">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Rol Carrera</th>
                                    <th>Rol</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th class="col-acciones">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $rol)
                                    @php
                                        $idRolCarrera = $rol->id_rol_carrera ?? $rol->id_rol ?? null;
                                    @endphp
                                    <tr>
                                        <td>{{ $idRolCarrera ?? 'N/D' }}</td>
                                        <td>{{ strtoupper($rol->nombre_rol ?? '') }}</td>
                                        <td>{{ $rol->descripcion ?? '' }}</td>
                                        <td>
                                            @if((int)($rol->estado_activo ?? 0) === 1)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($idRolCarrera)
                                                <button class="btn btn-primary btn-sm"
                                                        data-toggle="modal"
                                                        data-target="#modalEditarRol{{ $idRolCarrera }}">
                                                    Editar
                                                </button>
                                            @else
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    Sin ID
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No hay roles registrados para tu carrera.
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
                                <label class="form-label fw-bold">Rol:</label>
                                <select name="id_rol_carrera" class="form-select" required>
                                    <option value="">- SELECCIONE ROL -</option>
                                    @foreach($roles as $rol)
                                        @php
                                            $idRolCarrera = $rol->id_rol_carrera ?? $rol->id_rol ?? null;
                                        @endphp
                                        @if($idRolCarrera)
                                            <option value="{{ $idRolCarrera }}">
                                                {{ strtoupper($rol->nombre_rol ?? '') }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label fw-bold">Objeto:</label>
                                <select name="id_objeto_carrera" class="form-select" required>
                                    <option value="">- SELECCIONE OBJETO -</option>
                                    @foreach($objetos as $objeto)
                                        <option value="{{ $objeto->id_objeto_carrera }}">
                                            {{ strtoupper($objeto->nombre_objeto) }} ({{ strtoupper($objeto->tipo_objeto) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 col-lg-4">
                                <label class="form-label fw-bold">Permisos:</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach($permisos as $permiso)
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="permisos[]"
                                                   value="{{ $permiso->id_permiso }}"
                                                   id="permiso{{ $permiso->id_permiso }}">
                                            <label class="form-check-label" for="permiso{{ $permiso->id_permiso }}">
                                                {{ strtoupper($permiso->nombre_permiso) }}
                                            </label>
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
                    <span class="fw-bold text-white">Asignaciones Actuales por Carrera</span>
                </div>

                <div class="card-body bg-white">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Rol</th>
                                    <th>Permiso</th>
                                    <th>Objeto</th>
                                    <th>Fecha</th>
                                    <th class="col-acciones-sm">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rolPermisos as $asignacion)
                                    <tr>
                                        <td>{{ $asignacion->id_rol_permiso_carrera }}</td>
                                        <td>{{ $asignacion->nombre_rol }}</td>
                                        <td>{{ $asignacion->nombre_permiso }}</td>
                                        <td>{{ $asignacion->nombre_objeto }}</td>
                                        <td>{{ $asignacion->fecha_asignacion }}</td>
                                        <td>
                                            <form action="{{ route('seguridad.asignacion.delete', $asignacion->id_rol_permiso_carrera) }}"
                                                  method="POST"
                                                  class="js-confirm-delete"
                                                  data-confirm="¿Seguro que deseas desactivar esta asignación?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Desactivar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No hay asignaciones activas para tu carrera.
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

{{-- MODAL NUEVO ROL --}}
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

                <div class="modal-body bg-white">
                    <div class="form-group">
                        <label class="form-label fw-bold">Nombre del Rol:</label>
                        <input type="text" name="nombre_rol" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label fw-bold">Descripción:</label>
                        <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label fw-bold">Estado:</label>
                        <select name="estado_activo" class="form-select" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODALES EDITAR --}}
@foreach($roles as $rol)
    @php
        $idRolCarrera = $rol->id_rol_carrera ?? $rol->id_rol ?? null;
    @endphp

    @if($idRolCarrera)
        <div class="modal fade" id="modalEditarRol{{ $idRolCarrera }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content shadow">
                    <form action="{{ route('seguridad.rol.update', $idRolCarrera) }}" method="POST" class="js-confirm-submit" data-confirm="¿Deseas actualizar este rol?">
                        @csrf
                        @method('PUT')

                        <div class="modal-header security-header">
                            <h5 class="modal-title text-white">Editar Rol</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body bg-white">
                            <div class="form-group">
                                <label class="form-label fw-bold">Nombre del Rol:</label>
                                <input type="text" name="nombre_rol" class="form-control" value="{{ $rol->nombre_rol ?? '' }}" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label fw-bold">Descripción:</label>
                                <textarea name="descripcion" class="form-control" rows="3" required>{{ $rol->descripcion ?? '' }}</textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label fw-bold">Estado:</label>
                                <select name="estado_activo" class="form-select" required>
                                    <option value="1" {{ (int)($rol->estado_activo ?? 0) === 1 ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ (int)($rol->estado_activo ?? 0) === 0 ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <div class="modal-footer bg-white">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection