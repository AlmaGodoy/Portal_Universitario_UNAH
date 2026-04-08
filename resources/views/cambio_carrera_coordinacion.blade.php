@extends('layouts.app-coordinador') 
{{-- CAMBIO: ahora usa el layout general del sistema --}}

@section('titulo', 'Coordinación - Cambio de Carrera')

@section('content')

{{-- ============================= --}}
{{-- HEADER DEL MÓDULO --}}
{{-- ============================= --}}
<div class="cc-header">
    <h2>Coordinación - Cambio de Carrera</h2>
    <p>Gestión de dictamen final de trámites.</p>
</div>

{{-- ============================= --}}
{{-- CONTENIDO --}}
{{-- ============================= --}}
<div class="cc-container">

    <div class="card main-card">

        <div class="card-head">
            <div>
                <h3>Trámites pendientes de dictamen</h3>
                <p>
                    Aquí Coordinación revisará la información validada por Secretaría y emitirá el dictamen final.
                </p>
            </div>

            <span class="badge-soft">Coordinación</span>
        </div>

        <div id="msg" class="msg"></div>

        <table>
            <thead>
                <tr>
                    <th>ID Trámite</th>
                    <th>Fecha</th>
                    <th>Nombre del Estudiante</th>
                    <th>Carrera Destino</th>
                    <th>Estado Trámite</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="tbodyCoordinacion">
                <tr>
                    <td colspan="7">Cargando trámites...</td>
                </tr>
            </tbody>
        </table>

    </div>
</div>

{{-- JS ORIGINAL (NO TOCAR LÓGICA) --}}
@vite('resources/js/cambio_carrera_coordinacion.js')

@endsection