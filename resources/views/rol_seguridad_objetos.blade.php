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
            <h2 class="fw-bold">Gestión de Objetos</h2>
            <p class="text-muted mb-0">Administración de módulos y submódulos de seguridad.</p>
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

    <div class="card shadow border-0">
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

{{-- MODAL NUEVO OBJETO --}}
<div class="modal fade" id="modalNuevoObjeto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <form action="{{ route('seguridad.objeto.store') }}" method="POST">
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

{{-- MODALES EDITAR --}}
@foreach($objetos as $objeto)
<div class="modal fade" id="modalEditarObjeto{{ $objeto->id_objeto }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <form action="{{ route('seguridad.objeto.update', $objeto->id_objeto) }}" method="POST">
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
