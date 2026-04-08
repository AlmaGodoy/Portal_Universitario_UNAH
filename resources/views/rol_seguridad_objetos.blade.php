@extends('layouts.app-coordinador')

@section('titulo', 'Gestión de Objetos')

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

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="security-title">Gestión de Objetos</h2>
            <p class="security-subtitle">Administración de módulos y submódulos de seguridad.</p>
        </div>

        <div>
            <a href="{{ route('seguridad.index') }}" class="btn btn-outline-secondary">
                Volver a Seguridad
            </a>

            <button class="btn btn-primary ms-2" data-toggle="modal" data-target="#modalNuevoObjeto">
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
                            <th class="col-acciones-sm">Acciones</th>
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
                                            data-toggle="modal"
                                            data-target="#modalEditarObjeto{{ $objeto->id_objeto }}">
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

<div class="modal fade" id="modalNuevoObjeto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow">
            <form action="{{ route('seguridad.objeto.store') }}" method="POST" class="js-confirm-submit" data-confirm="¿Deseas guardar este objeto?">
                @csrf

                <div class="modal-header security-header">
                    <h5 class="modal-title text-white">Nuevo Objeto</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
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
<div class="modal fade" id="modalEditarObjeto{{ $objeto->id_objeto }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow">
            <form action="{{ route('seguridad.objeto.update', $objeto->id_objeto) }}" method="POST" class="js-confirm-submit" data-confirm="¿Deseas guardar los cambios de este objeto?">
                @csrf
                @method('PUT')

                <div class="modal-header security-header">
                    <h5 class="modal-title text-white">Editar Objeto</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
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
@endsection
