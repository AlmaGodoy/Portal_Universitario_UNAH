Modulo-Cambio-de-carrera
 Modulo-Cambio-de-carrera
 main
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Carrera</title>

Modulo-Cambio-de-carrera
    {{-- ✅ CARGA CORRECTA CON VITE --}}
    @vite(['resources/css/cambio_carrera.css', 'resources/js/cambio_carrera.js'])

    <link rel="stylesheet" href="{{ asset('css/cambio_carrera.css') }}">
 main
</head>
<body>

<div class="card">
    <h2>Solicitud de Cambio de Carrera</h2>

    <p class="info">
        Completa el formulario. Al crear el trámite, se habilitará la sección para subir tu <b>Historial Académico (PDF)</b>.
    </p>

    <form id="formCambioCarrera">
        <!-- (TEMPORAL) mientras no hay login -->
        <input type="hidden" id="id_persona" value="12">
Modulo-Cambio-de-carrera

main
        <input type="hidden" id="id_calendario" value="">

        <label for="id_carrera_destino">Carrera destino</label>
        <select id="id_carrera_destino" required>
            <option value="">Cargando carreras...</option>
        </select>

 Modulo-Cambio-de-carrera
        <label for="direccion">Justificación por la cual solicita el cambio de carrera</label>
        <input type="text" id="direccion" placeholder="justificación" required>

        <label for="direccion">Dirección / Departamento</label>
        <input type="text" id="direccion" placeholder="Ej: Departamento de informática" required>
 main

        <button type="submit" id="btnCrearTramite">Crear trámite</button>
    </form>

    <hr>

    <div id="seccionHistorial" style="display:none;">
        <h3>Subir Historial Académico (PDF)</h3>

        <p class="info">
            Sube el PDF de tu historial académico. Este documento respalda las validaciones del trámite.
        </p>

        <form id="formHistorial" enctype="multipart/form-data">
            <input type="hidden" id="id_tramite" value="">

            <label for="archivo">Selecciona tu PDF</label>
            <input type="file" id="archivo" accept="application/pdf" required>

            <button type="submit" id="btnSubirPDF">Subir PDF</button>
        </form>
    </div>

    <div id="msg" class="msg"></div>

    <hr>

    <div class="registroBox">
        <p><b>Nota:</b> Este sistema es de seguimiento. Para completar el proceso oficial, también debes realizarlo en Registro UNAH.</p>
        <a class="btnLink" href="https://registro.unah.edu.hn/" target="_blank">Ir a Registro UNAH</a>
Modulo-Cambio-de-carrera
    </div>
</div>

</body>
</html>

    </div>
</div>

<script src="{{ asset('js/cambio_carrera.js') }}" defer></script>
</body>
</html>

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
main
 main
