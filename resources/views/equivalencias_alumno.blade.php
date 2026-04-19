@extends('layouts.app-estudiantes')

@section('titulo', 'Equivalencias')

@section('content')
<div class="container-fluid py-3">

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-4" style="background: linear-gradient(135deg, #0d47a1 0%, #1565c0 100%); color: #fff; border-radius: 18px;">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <h2 class="fw-bold mb-2">Solicitud de Equivalencias</h2>
                            <p class="mb-0" style="max-width: 780px;">
                                Sube tu historial académico, selecciona tu plan anterior y registra las asignaturas aprobadas.
                                El sistema mostrará equivalencias preliminares para que luego sean revisadas y validadas oficialmente.
                            </p>
                        </div>

                        <div>
                            <a href="{{ url()->previous() }}" class="btn btn-light rounded-pill px-4 fw-semibold">
                                <i class="fas fa-arrow-left me-2"></i>Volver atrás
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ALERTA GLOBAL --}}
        <div class="col-12">
            <div id="eqAlertWrapper"></div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-upload text-primary"></i>
                        <h5 class="mb-0 fw-bold">Nueva solicitud</h5>
                    </div>
                    <small class="text-muted">Completa los datos y adjunta tu historial académico.</small>
                </div>

                <div class="card-body">
                    <form id="formSolicitudEquivalencia" enctype="multipart/form-data" novalidate>
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="version_plan_viejo" class="form-label fw-semibold">Plan viejo</label>
                                <select id="version_plan_viejo" name="version_plan_viejo" class="form-select">
                                    <option value="2019">Plan 2019</option>
                                    <option value="2022" selected>Plan 2022</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="version_plan_nuevo" class="form-label fw-semibold">Plan nuevo</label>
                                <input
                                    type="text"
                                    id="version_plan_nuevo"
                                    name="version_plan_nuevo"
                                    class="form-control"
                                    value="2026"
                                    readonly
                                >
                            </div>

                            <div class="col-12">
                                <label for="documento" class="form-label fw-semibold">Historial académico (PDF/JPG/PNG)</label>
                                <input
                                    type="file"
                                    id="documento"
                                    name="documento"
                                    class="form-control"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                >
                                <small class="text-muted d-block mt-2">
                                    Adjunta un archivo legible y actualizado de tu historial académico.
                                </small>
                            </div>

                            <div class="col-12">
                                <label for="observacion_alumno" class="form-label fw-semibold">Observación</label>
                                <textarea
                                    id="observacion_alumno"
                                    name="observacion_alumno"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Opcional"
                                ></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" id="btnCrearSolicitud" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                    <i class="fas fa-paper-plane me-2"></i>Crear solicitud
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-folder-open text-primary"></i>
                        <h5 class="mb-0 fw-bold">Mis solicitudes</h5>
                    </div>
                    <small class="text-muted">Consulta el estado general de tus solicitudes registradas.</small>
                </div>

                <div class="card-body">
                    <div id="equivalenciasMisSolicitudes">
                        <div class="text-center py-5 border rounded-4 text-muted">
                            <i class="fas fa-folder-open mb-3 fa-lg"></i>
                            <p class="mb-0">Cargando solicitudes...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BLOQUE DE ASIGNATURAS --}}
        <div class="col-12">
            <div id="bloqueAsignaturas" class="card border-0 shadow-sm d-none">
                <div class="card-header bg-white border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-book text-primary"></i>
                        <h5 class="mb-0 fw-bold">Asignaturas del plan viejo</h5>
                    </div>
                    <small class="text-muted">Selecciona las materias aprobadas que deseas registrar en la solicitud.</small>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">Sel.</th>
                                    <th>Código</th>
                                    <th>Asignatura</th>
                                    <th>UV</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAsignaturasPlanViejo">
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Aún no se han cargado asignaturas.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <button type="button" id="btnGuardarMaterias" class="btn btn-success rounded-pill px-4 fw-semibold">
                            <i class="fas fa-save me-2"></i>Guardar materias
                        </button>

                        <button type="button" id="btnVerPreliminares" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                            <i class="fas fa-eye me-2"></i>Ver equivalencias preliminares
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- BLOQUE DE EQUIVALENCIAS PRELIMINARES --}}
        <div class="col-12">
            <div id="bloquePreliminares" class="card border-0 shadow-sm d-none">
                <div class="card-header bg-white border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-random text-primary"></i>
                        <h5 class="mb-0 fw-bold">Equivalencias preliminares</h5>
                    </div>
                    <small class="text-muted">
                        Estas equivalencias son preliminares y estarán sujetas a revisión oficial.
                    </small>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Código viejo</th>
                                    <th>Asignatura vieja</th>
                                    <th>Código nuevo</th>
                                    <th>Asignatura nueva</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaEquivalenciasPreliminares">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Aún no hay equivalencias preliminares para mostrar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            Recuerda que estas equivalencias pueden cambiar durante la revisión por Secretaría o Coordinación.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection