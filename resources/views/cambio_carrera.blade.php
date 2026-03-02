@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/cambio_carrera.css') }}">

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-2">Cambio de Carrera</h3>
            <p class="text-muted mb-4">Crear, consultar, actualizar estado y cancelar solicitudes.</p>

            <meta name="csrf-token" content="{{ csrf_token() }}">

            {{-- TABS --}}
            <ul class="nav nav-tabs" id="ccTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-crear" data-bs-toggle="tab" data-bs-target="#pane-crear" type="button" role="tab">
                        Crear solicitud
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-consultar" data-bs-toggle="tab" data-bs-target="#pane-consultar" type="button" role="tab">
                        Consultar
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-estado" data-bs-toggle="tab" data-bs-target="#pane-estado" type="button" role="tab">
                        Actualizar estado
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-cancelar" data-bs-toggle="tab" data-bs-target="#pane-cancelar" type="button" role="tab">
                        Cancelar (soft delete)
                    </button>
                </li>
            </ul>

            <div class="tab-content pt-4">

                {{-- CREAR --}}
                <div class="tab-pane fade show active" id="pane-crear" role="tabpanel">
                    <form id="formCrear" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">ID Persona</label>
                            <input type="number" class="form-control" name="id_persona" placeholder="Ej: 12" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">ID Calendario</label>
                            <input type="number" class="form-control" name="id_calendario" placeholder="Ej: 1" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">ID Carrera Destino</label>
                            <input type="number" class="form-control" name="id_carrera_destino" placeholder="Ej: 3" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Dirección / Departamento</label>
                            <input type="text" class="form-control" name="direccion" placeholder="Ej: Departamento de informática" required>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Crear</button>
                            <button class="btn btn-outline-secondary" type="reset">Limpiar</button>
                        </div>
                    </form>
                </div>

                {{-- CONSULTAR --}}
                <div class="tab-pane fade" id="pane-consultar" role="tabpanel">
                    <form id="formConsultar" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Código</label>
                            <small class="text-muted d-block">Puede ser <b>ID Trámite</b> o <b>ID Persona</b> (según tu SP).</small>
                            <input type="number" class="form-control" name="codigo" placeholder="Ej: 3 o 12" required>
                        </div>

                        <div class="col-12">
                            <button class="btn btn-success" type="submit">Consultar</button>
                        </div>
                    </form>

                    <hr>
                    <div id="resultadoConsulta" class="resultado-box"></div>
                </div>

                {{-- ACTUALIZAR ESTADO --}}
                <div class="tab-pane fade" id="pane-estado" role="tabpanel">
                    <form id="formEstado" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ID Trámite</label>
                            <input type="number" class="form-control" name="id_tramite" placeholder="Ej: 3" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nuevo estado</label>
                            <input type="text" class="form-control" name="estado" placeholder="Ej: APROBADO / DENEGADO / DEVUELTO" required>
                        </div>

                        <div class="col-12">
                            <button class="btn btn-warning" type="submit">Actualizar</button>
                        </div>
                    </form>
                </div>

                {{-- CANCELAR / SOFT DELETE --}}
                <div class="tab-pane fade" id="pane-cancelar" role="tabpanel">
                    <form id="formCancelar" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ID Trámite</label>
                            <input type="number" class="form-control" name="id_tramite" placeholder="Ej: 3" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-danger" type="submit">Cancelar</button>
                        </div>
                    </form>
                </div>

            </div>

            <hr class="my-4">

            <div id="alertas"></div>
            <div id="respuesta" class="resultado-box"></div>

        </div>
    </div>
</div>

<script src="{{ asset('js/cambio_carrera.js') }}"></script>
@endsection