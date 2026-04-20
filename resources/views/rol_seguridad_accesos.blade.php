@extends('layouts.app-coordinador')

@section('titulo', 'Gestión de Accesos')
@section('hide_topbar', true)
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
                    <h2 class="security-title">Gestión de Accesos</h2>
                    <p class="security-subtitle">Asignación de permisos por rol y objeto dentro de tu carrera.</p>
                </div>

                <a href="{{ route('seguridad.index') }}" class="btn btn-outline-secondary">
                    Volver a Seguridad
                </a>
            </div>

            <div class="card shadow border-0 security-card">
                <div class="card-header security-header">
                    <span class="fw-bold text-white">Asignar Nuevo Acceso</span>
                </div>

                <div class="card-body bg-white">
                    <form action="{{ route('seguridad.acceso.store') }}" method="POST" class="js-confirm-submit" data-confirm="¿Deseas asignar este acceso?">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Rol:</label>
                                <select name="id_rol_carrera" class="form-select" required>
                                    <option value="">- SELECCIONE ROL -</option>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->id_rol_carrera }}">
                                            {{ strtoupper($rol->nombre_rol) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
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

                            <div class="col-md-4">
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

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    Guardar Acceso
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow border-0 security-card">
                <div class="card-header security-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-bold text-white">Lista de Accesos por Carrera</span>

                    <span class="badge bg-light text-dark">
                        Total accesos: {{ count($accesos) }}
                    </span>
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
                                    <th>Tipo Objeto</th>
                                    <th>Fecha</th>
                                    <th class="col-acciones-sm">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($accesos as $acceso)
                                    <tr>
                                        <td>{{ $acceso->id_rol_permiso_carrera }}</td>
                                        <td>{{ $acceso->nombre_rol }}</td>
                                        <td>{{ $acceso->nombre_permiso }}</td>
                                        <td>{{ $acceso->nombre_objeto }}</td>
                                        <td>{{ $acceso->tipo_objeto }}</td>
                                        <td>{{ $acceso->fecha_asignacion }}</td>
                                        <td>
                                            <form action="{{ route('seguridad.acceso.delete', $acceso->id_rol_permiso_carrera) }}"
                                                  method="POST"
                                                  class="js-confirm-delete"
                                                  data-confirm="¿Seguro que deseas desactivar este acceso?">
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
                                        <td colspan="7" class="text-center text-muted">
                                            No hay accesos registrados para tu carrera.
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
@endsection


