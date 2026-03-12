@extends('layouts.app')

@section('content')
@vite(['resources/css/cambio_carrera.css', 'resources/js/cambio_carrera.js'])

<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Gestión de Cambio de Carrera</h4>
        </div>
        <div class="card-body">
            <p class="text-muted">Portal para la creación y seguimiento de trámites académicos.</p>


            <ul class="nav nav-pills mb-4" id="ccTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="tab-crear" data-bs-toggle="tab" data-bs-target="#pane-crear" type="button">Crear Solicitud</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-consultar" data-bs-toggle="tab" data-bs-target="#pane-consultar" type="button">Consultar</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-estado" data-bs-toggle="tab" data-bs-target="#pane-estado" type="button">Estado</button>
                </li>
            </ul>

            <div class="tab-content">

                <div class="tab-pane fade show active" id="pane-crear" role="tabpanel">
                    <form id="formCrear" class="row g-3">
                        @csrf

                        <div class="col-md-4">
                            <label class="form-label fw-bold">ID Persona</label>
                            <input type="number" class="form-control bg-light" name="id_persona" id="id_persona" value="12" readonly>
                            <small class="text-muted">ID temporal de sesión</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Calendario Académico</label>
                            <select class="form-select" name="id_calendario" id="id_calendario" required>
                                <option value="">Seleccione el periodo...</option>
                                <option value="1">Primer Período 2026</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Carrera Destino</label>
                            <select class="form-select" name="id_carrera_destino" id="id_carrera_destino" required>
                                <option value="">Cargando carreras...</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Justificación del Cambio</label>
                            <textarea class="form-control" name="justificacion" id="justificacion" rows="2" placeholder="Explique brevemente los motivos..." required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Departamento / Facultad</label>
                            <input type="text" class="form-control" name="direccion" id="direccion" placeholder="Ej: Facultad de Ciencias Económicas" required>
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary px-4" type="submit" id="btnCrearTramite">
                                <i class="bi bi-plus-circle"></i> Crear Trámite
                            </button>
                        </div>
                    </form>

                    {{-- SECCIÓN PDF --}}
                    <div id="seccionHistorial" class="mt-4 p-3 border rounded border-warning bg-light" style="display:none;">
                        <h5 class="text-warning"><i class="bi bi-file-earmark-pdf"></i> Paso Final: Adjuntar Historial</h5>
                        <form id="formHistorial">
                            <input type="hidden" name="id_tramite" id="id_tramite_input">
                            <div class="input-group">
                                <input type="file" class="form-control" id="archivo_pdf" accept=".pdf" required>
                                <button class="btn btn-success" type="submit">Subir Archivo</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- PANE: CONSULTAR --}}
                <div class="tab-pane fade" id="pane-consultar" role="tabpanel">
                    <div class="input-group mb-3" style="max-width: 400px;">
                        <input type="number" class="form-control" id="codigo_busqueda" placeholder="Ingrese ID de Trámite">
                        <button class="btn btn-success" id="btnConsultar">Buscar</button>
                    </div>
                    <div id="resultadoConsulta" class="mt-3"></div>
                </div>

                {{-- PANE: ESTADO --}}
                <div class="tab-pane fade" id="pane-estado" role="tabpanel">
                    <div class="alert alert-secondary">Módulo de actualización para coordinadores.</div>
                </div>
            </div>

            <div id="alertas" class="mt-3"></div>
        </div>
    </div>
</div>
@endsection
