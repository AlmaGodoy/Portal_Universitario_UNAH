@extends('layouts.app-estudiantes')

@section('titulo', 'Equivalencias')

@push('styles')
    @vite(['resources/css/equivalencias.css'])
@endpush

@section('content')
<div class="container-fluid equivalencias-alumno-page">

    {{-- Encabezado --}}
    <div class="eqal-header card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="eqal-header__top">
                <div>
                    <h1 class="eqal-title">Solicitud de Equivalencias</h1>
                    <p class="eqal-subtitle mb-0">
                        Sube tu historial académico, selecciona tu plan anterior y registra las asignaturas aprobadas.
                        El sistema mostrará equivalencias preliminares para que luego sean revisadas y validadas oficialmente.
                    </p>
                </div>

                <div class="eqal-actions-top">
                    <a href="{{ url()->previous() }}" class="btn eqal-btn-back">
                        <i class="fas fa-arrow-left me-2"></i>
                        Volver atrás
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas dinámicas --}}
    <div id="eqAlertWrapper"></div>

    <div class="row g-4">

        {{-- Nueva solicitud --}}
        <div class="col-12 col-lg-5">
            <div class="card eqal-card border-0 shadow-sm h-100">
                <div class="card-header eqal-card__header">
                    <h3 class="eqal-card__title mb-0">
                        <i class="fas fa-upload me-2"></i>
                        Nueva solicitud
                    </h3>
                    <p class="eqal-card__subtitle mb-0">
                        Completa los datos y adjunta tu historial académico.
                    </p>
                </div>

                <div class="card-body">
                    <form id="formSolicitudEquivalencia" enctype="multipart/form-data" novalidate>
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="version_plan_viejo" class="form-label eqal-form-label">
                                    Plan viejo
                                </label>
                                <select id="version_plan_viejo" name="version_plan_viejo" class="form-select eqal-input">
                                    <option value="2019">Plan 2019</option>
                                    <option value="2022" selected>Plan 2022</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="version_plan_nuevo" class="form-label eqal-form-label">
                                    Plan nuevo
                                </label>
                                <input
                                    type="text"
                                    id="version_plan_nuevo"
                                    name="version_plan_nuevo"
                                    class="form-control eqal-input"
                                    value="2026"
                                    readonly
                                >
                            </div>

                            <div class="col-12">
                                <label for="documento" class="form-label eqal-form-label">
                                    Historial académico
                                </label>
                                <input
                                    type="file"
                                    id="documento"
                                    name="documento"
                                    class="form-control eqal-input eqal-file-input"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                >
                                <small class="eqal-help d-block mt-2">
                                    Adjunta un archivo legible y actualizado en formato PDF, JPG o PNG.
                                </small>
                            </div>

                            <div class="col-12">
                                <label for="observacion_alumno" class="form-label eqal-form-label">
                                    Observación
                                </label>
                                <textarea
                                    id="observacion_alumno"
                                    name="observacion_alumno"
                                    class="form-control eqal-input eqal-textarea"
                                    rows="4"
                                    placeholder="Opcional"
                                ></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" id="btnCrearSolicitud" class="btn eqal-btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Crear solicitud
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Mis solicitudes --}}
        <div class="col-12 col-lg-7">
            <div class="card eqal-card border-0 shadow-sm h-100">
                <div class="card-header eqal-card__header">
                    <h3 class="eqal-card__title mb-0">
                        <i class="fas fa-folder-open me-2"></i>
                        Mis solicitudes
                    </h3>
                    <p class="eqal-card__subtitle mb-0">
                        Consulta el estado general de tus solicitudes registradas.
                    </p>
                </div>

                <div class="card-body">
                    <div id="equivalenciasMisSolicitudes">
                        <div class="eqal-empty">
                            <i class="fas fa-folder-open"></i>
                            <p class="mb-0">Cargando solicitudes...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Asignaturas del plan viejo --}}
        <div class="col-12">
            <div id="bloqueAsignaturas" class="card eqal-card border-0 shadow-sm d-none">
                <div class="card-header eqal-card__header">
                    <h3 class="eqal-card__title mb-0">
                        <i class="fas fa-book me-2"></i>
                        Asignaturas del plan viejo
                    </h3>
                    <p class="eqal-card__subtitle mb-0">
                        Selecciona las materias aprobadas que deseas registrar en la solicitud.
                    </p>
                </div>

                <div class="card-body">
                    <div class="eqal-table-wrapper">
                        <table class="table eqal-table align-middle mb-0">
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
                                    <td colspan="4" class="eqal-empty-row">
                                        Aún no se han cargado asignaturas.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="eqal-actions">
                        <button type="button" id="btnGuardarMaterias" class="btn eqal-btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Guardar materias
                        </button>

                        <button type="button" id="btnVerPreliminares" class="btn eqal-btn-outline">
                            <i class="fas fa-eye me-2"></i>
                            Ver equivalencias preliminares
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Equivalencias preliminares --}}
        <div class="col-12">
            <div id="bloquePreliminares" class="card eqal-card border-0 shadow-sm d-none">
                <div class="card-header eqal-card__header">
                    <h3 class="eqal-card__title mb-0">
                        <i class="fas fa-random me-2"></i>
                        Equivalencias preliminares
                    </h3>
                    <p class="eqal-card__subtitle mb-0">
                        Estas equivalencias son preliminares y estarán sujetas a revisión oficial.
                    </p>
                </div>

                <div class="card-body">
                    <div class="eqal-table-wrapper">
                        <table class="table eqal-table align-middle mb-0">
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
                                    <td colspan="5" class="eqal-empty-row">
                                        Aún no hay equivalencias preliminares para mostrar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="eqal-note mt-3">
                        <i class="fas fa-circle-info"></i>
                        <span>
                            Recuerda que estas equivalencias pueden cambiar durante la revisión por Secretaría o Coordinación.
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection