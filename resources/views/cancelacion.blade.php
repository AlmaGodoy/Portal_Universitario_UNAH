@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow border-0" id="cardPaso1">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i>Paso 1: Solicitud de Cancelación Digital</h4>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle me-1"></i> Según los <strong>Artículos 222-224</strong>, esta solicitud será evaluada por su Coordinación de Carrera en un máximo de 3 días hábiles.</small>
                    </div>

                    <form id="formCancelacion">
                        @csrf
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nombre Completo</label>
                                <input type="text" class="form-control bg-light" name="nombre_estudiante" value="Alma Patricia Godoy Cruz" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Correo Institucional</label>
                                <input type="email" class="form-control bg-light" name="email" value="alma.godoy@unah.hn" readonly>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Prioridad de la Solicitud</label>
                                <select name="prioridad" id="prioridad" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Alta">Alta (Casos de emergencia)</option>
                                    <option value="Media">Media</option>
                                    <option value="Baja">Baja</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tipo de Cancelación</label>
                                <select name="tipo_cancelacion" class="form-select" required>
                                    <option value="Parcial">Parcial (Solo algunas asignaturas)</option>
                                    <option value="Total">Total (Todo el período)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Explicación de los Hechos (Art. 224)</label>
                            <textarea name="observacion_inicial" id="observacion_inicial" class="form-control" rows="4"
                                placeholder="Describa brevemente la razón de su cancelación..." required></textarea>
                            <div class="form-text">Esta descripción sustituye el cuadro de 'Hechos' del formato físico.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="btnSiguiente">
                                Continuar a Carga de Documentos <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow border-0 mt-4 d-none" id="cardPaso2">
                <div class="card-header bg-success text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-file-upload me-2"></i>Paso 2: Requisitos y Evidencias</h4>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <span class="badge bg-secondary px-3 py-2">ID Trámite: <span id="displayIdTramite"></span></span>
                    </div>

                    <form id="formDocumento" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id_tramite" id="id_tramite_hidden">

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-primary">1. Identificación (DNI)</label>
                                <input type="file" name="archivo_dni" class="form-control" accept="image/*,.pdf" required>
                                <div class="form-text">Copia legible por ambos lados.</div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-primary">2. Forma 003</label>
                                <input type="file" name="archivo_forma" class="form-control" accept=".pdf" required>
                                <div class="form-text">Descargada de la página de Registro.</div>
                            </div>

                            <hr>

                            <div class="col-12 mb-4">
                                <label class="form-label fw-bold text-danger">3. Documentación de Respaldo (Evidencia)</label>
                                <input type="file" name="archivo_pdf" id="archivo_pdf" class="form-control" accept=".pdf" required>
                                <div class="form-text">
                                    Suba el respaldo correspondiente a su causa:
                                    <ul class="mt-1">
                                        []<li><strong>Salud:</strong> Certificación médica oficial[cite: 74].</li>
                                        []<li><strong>Trabajo:</strong> Constancia de la Jefatura de Personal[cite: 78].</li>
                                        []<li><strong>Calamidad:</strong> Acta de defunción o certificación del RNP[cite: 77].</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="declaracion" required>
                                <label class="form-check-label" for="declaracion">
                                    <strong>Declaración Jurada:</strong> Declaro que la información y documentos adjuntos son verídicos. Entiendo que esto equivale a mi firma física en el proceso institucional.
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">Finalizar Trámite Digital</button>
                            <button type="button" class="btn btn-link text-muted" onclick="window.location.reload()">Cancelar y Empezar de Nuevo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/cancelacion.js'])
@endsection
