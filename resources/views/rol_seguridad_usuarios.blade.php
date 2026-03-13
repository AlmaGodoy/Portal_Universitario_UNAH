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

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold">Gestión de Usuarios</h2>
            <p class="text-muted mb-0">Administración de usuarios del sistema y su estado.</p>
        </div>

        <a href="{{ route('seguridad.index') }}" class="btn btn-outline-secondary">
            Volver a Seguridad
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-header security-header">
            <span class="fw-bold text-white">Lista de Usuarios</span>
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
                            <th>Estado Cuenta</th>
                            <th style="width: 140px;">Acciones</th>
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
                                <td>
                                    @if($usuario->estado_cuenta == 1)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEstadoUsuario{{ $usuario->id_usuario }}">
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@foreach($usuarios as $usuario)
<div class="modal fade" id="modalEstadoUsuario{{ $usuario->id_usuario }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <form action="{{ route('seguridad.usuario.estado', $usuario->id_usuario) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header security-header">
                    <h5 class="modal-title text-white">Actualizar Estado de Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                        <label class="form-label fw-bold">Estado de cuenta:</label>
                        <select name="estado_cuenta" class="form-select" required>
                            <option value="1" {{ $usuario->estado_cuenta == 1 ? 'selected' : '' }}>ACTIVO</option>
                            <option value="0" {{ $usuario->estado_cuenta == 0 ? 'selected' : '' }}>INACTIVO</option>
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

<style>
    .security-header {
        background: #3b82c4;
        color: white;
        border-bottom: none;
    }

    .input-highlight {
        background-color: #eef5be;
        font-weight: 600;
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
