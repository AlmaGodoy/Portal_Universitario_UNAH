@extends('layouts.app-estudiantes')

@section('titulo', 'Equivalencias')

@section('content')

<div class="eq-page">

    <div id="eqAlumnoAlert" class="eq-alert d-none"></div>

    <section class="eq-top-banner">
        <div class="eq-top-banner-copy">
            <h1>Solicitud de Equivalencias</h1>
            <p>
                Sube tu historial académico, selecciona tu plan anterior y registra las asignaturas aprobadas.
                El sistema mostrará equivalencias preliminares para que luego sean revisadas y validadas oficialmente.
            </p>
        </div>

        <div class="eq-dashboard-btn">
            <i class="fas fa-file-lines"></i>
            <span>Trámite guiado</span>
        </div>
    </section>

    <div class="eq-dashboard-grid">

        <div class="eq-left-column">
            <section class="eq-panel">
                <div class="eq-panel-head">
                    <div>
                        <h3><i class="fas fa-upload"></i> Nueva solicitud</h3>
                        <p>Completa los datos y adjunta tu historial académico.</p>
                    </div>
                </div>

                <div class="eq-panel-body">
                    <form id="formCrearSolicitud" class="eq-form" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="eq-form-grid">
                            <div class="eq-field">
                                <label for="version_plan_viejo">Plan viejo</label>
                                <select id="version_plan_viejo" name="version_plan_viejo" required>
                                    <option value="">Seleccione una opción</option>
                                    <option value="19">Plan 2019</option>
                                    <option value="2022">Plan 2022</option>
                                </select>
                            </div>

                            <div class="eq-field">
                                <label for="version_plan_nuevo">Plan nuevo</label>
                                <input
                                    id="version_plan_nuevo"
                                    name="version_plan_nuevo"
                                    type="number"
                                    value="2025"
                                    readonly
                                >
                            </div>

                            <div class="eq-field eq-field-full">
                                <label for="documento">Historial académico (PDF/JPG/PNG)</label>
                                <input
                                    id="documento"
                                    name="documento"
                                    type="file"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    required
                                >
                                <small class="eq-helper">
                                    Adjunta un archivo legible y actualizado de tu historial académico.
                                </small>
                            </div>

                            <div class="eq-field eq-field-full">
                                <label for="observacion_alumno">Observación</label>
                                <textarea
                                    id="observacion_alumno"
                                    name="observacion_alumno"
                                    rows="4"
                                    placeholder="Opcional"
                                ></textarea>
                            </div>
                        </div>

                        <div class="eq-actions">
                            <button type="submit" class="eq-btn eq-btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                Crear solicitud
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        <div class="eq-right-column">
            <section class="eq-panel">
                <div class="eq-panel-head">
                    <div>
                        <h3><i class="fas fa-folder-open"></i> Mis solicitudes</h3>
                        <p>Consulta el estado general de tus solicitudes registradas.</p>
                    </div>
                </div>

                <div class="eq-panel-body">
                    <div id="misSolicitudesWrap" class="eq-list-wrap">
                        <div class="eq-empty">
                            <i class="fas fa-inbox"></i>
                            <span>Aún no hay solicitudes registradas.</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

    </div>

    <section id="bloqueMaterias" class="eq-panel d-none">
        <div class="eq-panel-head">
            <div>
                <h3><i class="fas fa-list-check"></i> Materias aprobadas del plan viejo</h3>
                <p>Marca únicamente las asignaturas aprobadas que aparecen en tu historial.</p>
            </div>

            <div class="eq-pill-wrap">
                <span class="eq-pill">
                    Solicitud ID:
                    <strong id="currentSolicitudLabel">-</strong>
                </span>

                <span class="eq-pill">
                    Plan viejo:
                    <strong id="currentPlanViejoLabel">-</strong>
                </span>
            </div>
        </div>

        <div class="eq-table-wrap">
            <table class="eq-table">
                <thead>
                    <tr>
                        <th>Seleccionar</th>
                        <th>Código</th>
                        <th>Asignatura</th>
                        <th>UV</th>
                        <th>Nota final</th>
                    </tr>
                </thead>
                <tbody id="tablaAsignaturasPlanViejo">
                    <tr>
                        <td colspan="5" class="eq-empty-row">Primero crea una solicitud.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="eq-panel-body">
            <div class="eq-actions">
                <button id="btnGuardarDetalle" type="button" class="eq-btn eq-btn-primary">
                    <i class="fas fa-floppy-disk"></i>
                    Guardar materias
                </button>

                <button id="btnVerPreliminares" type="button" class="eq-btn eq-btn-secondary">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    Ver equivalencias preliminares
                </button>
            </div>
        </div>
    </section>

    <section id="bloquePreliminares" class="eq-panel d-none">
        <div class="eq-panel-head">
            <div>
                <h3><i class="fas fa-diagram-project"></i> Equivalencias preliminares</h3>
                <p>Estas equivalencias son preliminares hasta que el revisor valide la solicitud.</p>
            </div>
        </div>

        <div class="eq-table-wrap">
            <table class="eq-table">
                <thead>
                    <tr>
                        <th>Código viejo</th>
                        <th>Asignatura vieja</th>
                        <th>Nota</th>
                        <th>Código nuevo</th>
                        <th>Asignatura nueva</th>
                        <th>Situación</th>
                    </tr>
                </thead>
                <tbody id="tablaPreliminares">
                    <tr>
                        <td colspan="6" class="eq-empty-row">Aún no se han calculado equivalencias.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

</div>

@endsection