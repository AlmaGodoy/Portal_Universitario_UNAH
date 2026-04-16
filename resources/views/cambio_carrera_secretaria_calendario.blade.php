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
    <i class="fas fa-arrow-left"></i> Volver
</a>
        </div>
    </div>

    {{-- FORM CREAR --}}
    <div class="cc-card">
        <div class="cc-card-head">
            <h3>Crear nuevo calendario</h3>
        </div>

        <form id="formCalendario" class="cc-form">

            <div class="cc-form-group">
                <label>Tipo de trámite</label>
                <select id="tipo_tramite_academico" required>
                    <option value="">Seleccione...</option>
                    <option value="cambio_carrera">Cambio de carrera</option>
                    <option value="cancelacion">Cancelación</option>
                </select>
            </div>

            <div class="cc-form-group">
                <label>Fecha inicio</label>
                <input type="date" id="fecha_inicio" required>
            </div>

            <div class="cc-form-group">
                <label>Fecha fin</label>
                <input type="date" id="fecha_fin" required>
            </div>

            <button type="submit" class="cc-btn-primary">
                Crear calendario
            </button>

        </form>

        <div id="msg" class="msg"></div>
    </div>

    {{-- LISTADO --}}
    <div class="cc-card">
        <div class="cc-card-head">
            <h3>Listado de calendarios</h3>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyCalendarios">
                    <tr>
                        <td colspan="6">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- JS --}}
@vite(['resources/js/cambio_carrera_secretaria_calendario.js'])

@endsection