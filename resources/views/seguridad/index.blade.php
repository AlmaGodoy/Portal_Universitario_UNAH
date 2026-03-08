@extends('layouts.app')

@section('content')
<div class="container py-4">

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

        {{-- PANEL DE ROLES --}}
        <div class="col-lg-7">
            <div class="card shadow border-0">
                <div class="card-header security-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-white">Gestión de Roles</span>

                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoRol">
                        + Nuevo Rol
                    </button>
                </div>

                <div class="card-body bg-white">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th style="width: 170px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $rol)
                                    <tr>
                                        <td>{{ $rol->id_rol }}</td>
                                        <td>{{ strtoupper($rol->nombre_rol) }}</td>
                                        <td>
                                            @if($rol->estado_activo == 1)
                                                <span class="badge bg-success">A</span>
                                            @else
                                                <span class="badge bg-danger">I</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditarRol{{ $rol->id_rol }}">
                                                Editar
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- MODAL EDITAR ROL --}}
                                    <div class="modal fade" id="modalEditarRol{{ $rol->id_rol }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content shadow">
                                                <form action="{{ route('seguridad.rol.update', $rol->id_rol) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="modal-header security-header">
                                                        <h5 class="modal-title text-white">Editar Rol</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Rol:</label>
                                                            <input type="text" name="nombre_rol" class="form-control input-highlight" value="{{ strtoupper($rol->nombre_rol) }}" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Descripción:</label>
                                                            <textarea name="descripcion" class="form-control" rows="3" required>{{ $rol->descripcion }}</textarea>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Estado del Rol:</label>
                                                            <select name="estado_activo" class="form-select" required>
                                                                <option value="1" {{ $rol->estado_activo == 1 ? 'selected' : '' }}>ACTIVO</option>
                                                                <option value="0" {{ $rol->estado_activo == 0 ? 'selected' : '' }}>INACTIVO</option>
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
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No hay roles registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ASIGNACIÓN DE PERMISOS --}}
        <div class="col-lg-5">
            <div class="card shadow border-0">
                <div class="card-header security-header">
                    <span class="fw-bold text-white">Editar Tipo de Usuario</span>
                </div>

                <div class="card-body bg-white">
                    <form action="{{ route('seguridad.asignar.objeto') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción:</label>
                            <select name="id_rol" class="form-select" required>
                                <option value="">- SELECCIONE ROL -</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id_rol }}">{{ strtoupper($rol->nombre_rol) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Pantalla</label>
                            <select name="id_objeto" class="form-select" required>
                                <option value="">- SELECCIONE UNA PANTALLA -</option>
                                @foreach($objetos as $obj)
                                    <option value="{{ $obj->id_objeto }}">{{ strtoupper($obj->nombre_objeto) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">Nuevo Acceso:</label>

                            @php
                                $mapa = [];
                                foreach($permisos as $permiso){
                                    $mapa[strtolower($permiso->nombre_permiso)] = $permiso->id_permiso;
                                }
                            @endphp

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permisos[]" value="{{ $mapa['visualizar'] ?? '' }}" id="perm_visualizar">
                                <label class="form-check-label" for="perm_visualizar">Visualizar</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permisos[]" value="{{ $mapa['guardar'] ?? '' }}" id="perm_guardar">
                                <label class="form-check-label" for="perm_guardar">Guardar</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permisos[]" value="{{ $mapa['actualizar'] ?? '' }}" id="perm_actualizar">
                                <label class="form-check-label" for="perm_actualizar">Actualizar</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permisos[]" value="{{ $mapa['eliminar'] ?? '' }}" id="perm_eliminar">
                                <label class="form-check-label" for="perm_eliminar">Eliminar</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Agregar</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- TABLA DE ASIGNACIONES --}}
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header security-header">
                    <span class="fw-bold text-white">Roles de Usuarios Agregados</span>
                </div>

                <div class="card-body bg-white">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Rol</th>
                                    <th>Pantalla</th>
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
                                                  onsubmit="return confirm('¿Eliminar esta asignación?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Acción
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No hay asignaciones registradas.</td>
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
<div class="modal fade" id="modalNuevoRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <form action="{{ route('seguridad.rol.store') }}" method="POST">
                @csrf

                <div class="modal-header security-header">
                    <h5 class="modal-title text-white">Nuevo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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

<style>
    .security-header {
        background: #3b82c4;
        color: white;
        border-bottom: none;
    }

    .input-highlight {
        background-color: #eef5be;
        font-weight: 600;
        text-transform: uppercase;
    }

    .card {
        border-radius: 10px;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }
</style>
@endsection
