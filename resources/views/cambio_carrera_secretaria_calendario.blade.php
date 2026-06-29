@extends('layouts.app-secretaria')

@section('titulo', 'Gestión de Calendarios Académicos')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="cc-page">

    {{-- HEADER --}}
    <div class="cc-header">
        <div class="cc-header-content">
            <div>
                <h1>Calendarios Académicos</h1>
                <p>Gestión de fechas para Cambio de Carrera y Cancelación.</p>
            </div>

            <a href="{{ url('/empleado/dashboard') }}" class="cc-btn-volver">

                <i class="fas fa-arrow-left"></i> Regresar

                <i class="fas fa-arrow-left"></i> Volver

            </a>
        </div>
    </div>

    {{-- FORM CREAR --}}
    <div class="cc-card">
        <div class="cc-card-head">
            <h3>Crear nuevo calendario</h3>
            <p class="cc-card-subtitle">
                Define el período en el que los estudiantes podrán realizar solicitudes.
            </p>
        </div>

        <form id="formCalendario" class="cc-form">

            <div class="cc-form-group">
                <label for="tipo_tramite_academico">
                    Tipo de trámite <span class="required">*</span>
                </label>

                <select id="tipo_tramite_academico" required>
                    <option value="">Seleccione...</option>
                    <option value="cambio_carrera">Cambio de carrera</option>
                    <option value="cancelacion">Cancelación</option>
                </select>
            </div>

            <div class="cc-form-group">
                <label for="fecha_inicio">
                    Fecha inicio <span class="required">*</span>
                </label>

                <input type="date" id="fecha_inicio" required>
            </div>

            <div class="cc-form-group">
                <label for="fecha_fin">
                    Fecha fin <span class="required">*</span>
                </label>

                <input type="date" id="fecha_fin" required>
            </div>

            <button type="submit" class="cc-btn-primary">
                <i class="fas fa-calendar-plus"></i> Crear calendario
            </button>

        </form>

        <div id="msg" class="msg"></div>
    </div>

    {{-- LISTADO --}}
    <div class="cc-card">
        <div class="cc-card-head">
            <h3>Listado de calendarios</h3>
            <p class="cc-card-subtitle">
                Consulta los calendarios creados y administra su estado.
            </p>
        </div>

        <div class="cc-form-group" style="margin-bottom: 18px;">
    <label for="buscarCalendario">
        Buscar calendario
    </label>

    <input 
        type="text" 
        id="buscarCalendario" 
        class="form-control"
        placeholder="Buscar por identificación, tipo, fecha o estado..."
    >
</div>

        <div class="table-responsive">
            <table class="table table-bordered cc-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo de trámite</th>
                        <th>Fecha inicio</th>
                        <th>Fecha fin</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody id="tbodyCalendarios">
                    <tr>
                        <td colspan="8" class="text-center">
                            Cargando calendarios...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- JS --}}
@vite(['resources/js/cambio_carrera_secretaria_calendario.js'])

@endsection