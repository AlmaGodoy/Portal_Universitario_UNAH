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

        <div class="col-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="fw-bold mb-0">Gestión de Accesos</h2>
                    <p class="text-muted mb-0">Asignación de permisos por rol y objeto.</p>
                </div>

                <a href="{{ route('seguridad.index') }}" class="btn btn-outline-secondary">
                    Volver a Seguridad
                </a>
            </div>

            <div class="card shadow border-0">
                <div class="card-header security-header">
                    <span class="fw-bold text-white">Asignar Nuevo Acceso</span>
                </div>

                <div class="card-body bg-white">
                    <form action="{{ route('seguridad.acceso.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Rol:</label>
                            <select name="id_rol" class="form-select" required>
                                <option value="">- SELECCIONE ROL -</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id_rol }}">
                                        {{ strtoupper($rol->nombre_rol) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Permiso:</label>
                            <select name="id_permiso" class="form-select" required>
                                <option value="">- SELECCIONE PERMISO -</option>
                                @foreach($permisos as $permiso)
                                    <option value="{{ $permiso->id_permiso }}">
                                        {{ strtoupper($permiso->nombre_permiso) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Objeto:</label>
                            <select name="id_objeto" class="form-select" required>
                                <option value="">- SELECCIONE OBJETO -</option>
                                @foreach($objetos as $objeto)
                                    <option value="{{ $objeto->id_objeto }}">
                                        {{ strtoupper($objeto->nombre_objeto) }} ({{ strtoupper($objeto->tipo_objeto) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Asignar Acceso
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow border-0">
                <div class="card-header security-header">
                    <span class="fw-bold text-white">Accesos Registrados</span>
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
                                    <th>Tipo</th>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accesos as $acceso)
                                    <tr>
                                        <td>{{ $acceso->id_rol_permiso }}</td>
                                        <td>{{ $acceso->nombre_rol }}</td>
                                        <td>{{ $acceso->nombre_permiso }}</td>
                                        <td>{{ $acceso->nombre_objeto }}</td>
                                        <td>{{ $acceso->tipo_objeto }}</td>
                                        <td>{{ $acceso->fecha_asignacion }}</td>
                                        <td>
                                            <form action="{{ route('seguridad.acceso.delete', $acceso->id_rol_permiso) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Eliminar este acceso?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            No hay accesos registrados.
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

<style>
    .security-header {
        background: #3b82c4;
        color: white;
        border-bottom: none;
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
