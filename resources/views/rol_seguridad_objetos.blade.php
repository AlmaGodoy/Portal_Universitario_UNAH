@extends('layouts.app-coordinador')
@section('hide_topbar', true)
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

    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="security-title">Gestión de Objetos</h2>
            <p class="security-subtitle">Administración de objetos o módulos correspondientes únicamente a tu carrera.</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('seguridad.index') }}" class="btn btn-outline-secondary">
                Volver a Seguridad
            </a>

            <button class="btn btn-primary" data-toggle="modal" data-target="#modalNuevoObjeto">
                + Nuevo Objeto
            </button>
        </div>
    </div>

    <div class="card shadow border-0 security-card">
        <div class="card-header security-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold text-white">Lista de Objetos por Carrera</span>

            <span class="badge bg-light text-dark">
                Total objetos: {{ count($objetos) }}
            </span>
        </div>

        <div class="card-body bg-white">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID Objeto Carrera</th>
                            <th>Nombre del Objeto</th>
                            <th>Tipo de Objeto</th>
                            <th>Estado</th>
                            <th class="col-acciones-sm">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($objetos as $objeto)
                            <tr>
                                <td>{{ $objeto->id_objeto_carrera }}</td>
                                <td>{{ strtoupper($objeto->nombre_objeto) }}</td>
                                <td>{{ strtoupper($objeto->tipo_objeto) }}</td>
                                <td>
                                    @if((int)$objeto->estado_activo === 1)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm"
                                            data-toggle="modal"
                                            data-target="#modalEditarObjeto{{ $objeto->id_objeto_carrera }}">
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    No hay objetos registrados para tu carrera.
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

                <div class="modal-body bg-white">
                    <div class="form-group">
                        <label class="form-label fw-bold">Nombre del Objeto:</label>
                        <input type="text" name="nombre_objeto" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label fw-bold">Tipo del Objeto:</label>
                        <input type="text" name="tipo_objeto" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($objetos as $objeto)
<div class="modal fade" id="modalEditarObjeto{{ $objeto->id_objeto_carrera }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow">
            <form action="{{ route('seguridad.objeto.update', $objeto->id_objeto_carrera) }}" method="POST" class="js-confirm-submit" data-confirm="¿Deseas actualizar este objeto?">
                @csrf
                @method('PUT')

                <div class="modal-header security-header">
                    <h5 class="modal-title text-white">Editar Objeto</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body bg-white">
                    <div class="form-group">
                        <label class="form-label fw-bold">Nombre del Objeto:</label>
                        <input type="text" name="nombre_objeto" class="form-control" value="{{ $objeto->nombre_objeto }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label fw-bold">Tipo del Objeto:</label>
                        <input type="text" name="tipo_objeto" class="form-control" value="{{ $objeto->tipo_objeto }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label fw-bold">Estado:</label>
                        <select name="estado_activo" class="form-select" required>
                            <option value="1" {{ (int)$objeto->estado_activo === 1 ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ (int)$objeto->estado_activo === 0 ? 'selected' : '' }}>Inactivo</option>
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
@endforeach
@endsection


