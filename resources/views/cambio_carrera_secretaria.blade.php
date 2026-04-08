@extends('layouts.app-secretaria')

@section('titulo', 'Secretaría - Cambio de Carrera')

@section('content')
    {{-- CAMBIO: se deja solo el HTML de la vista.
         Ya NO lleva <!DOCTYPE html>, <html>, <head> ni <body>,
         porque eso lo pone el layout padre. --}}


    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="cc-page">
        <div class="cc-header">
            <div class="cc-header-content">
                <div>
                    <h1>Secretaría - Cambio de Carrera</h1>
                    <p>Aquí Secretaría podrá revisar los trámites pendientes de revisión.</p>
                </div>

           
                <a href="javascript:history.back()" class="cc-btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="cc-card">
            <div class="cc-card-head">
                <div>
                    <h3>Trámites pendientes de revisión</h3>
                    <p>
                        Aquí Secretaría podrá revisar el historial académico.
                    </p>
                </div>

                <span class="cc-badge">Secretaría</span>
            </div>

       
            <div id="msg" class="msg"></div>

            <div class="cc-table-wrap">
                <table class="cc-table">
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
                    <tbody id="tbodySecretaria">
                        <tr>
                            <td colspan="6">Cargando trámites...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection