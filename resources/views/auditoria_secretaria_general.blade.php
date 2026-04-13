@extends('layouts.app-secretaria')

@section('content')
@vite(['resources/css/auditoria.css', 'resources/js/auditoria.js'])

<div class="container py-4 security-page">
    <div class="row g-4">
        <div class="col-12">
                 <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                     <header class="topbar">
                         <div class="brand">
                             <img src="{{ asset('images/abejita.jpeg') }}" alt="Logo PumaGestión" class="brand-logo">


                                 <div class="brand-text">
                                     <h1 class="brand-title">Auditoria del Sistema</h1>
                                         <span class="brand-subtitle">FCEAC - UNAH</span>
                                 </div>
                         </div>
                     </header>


                     @if(session('error'))
                          <div style="color:red; margin-bottom: 10px;">
                               {{ session('error') }}
                          </div>
                      @endif

                     <!-- FORMULARIO DE CONSULTA -->
                     <form id="formBitacora" method="GET" action="{{ route('auditoria') }}" class="mb-3">
                             <div >
                                 <label>Fecha Inicial</label>
                                     <input
                                         type="date"
                                         name="fecha_inicial"
                                         class="form-control"
                                         value="{{ $fechaInicial }}">
                             </div>

                             <div >
                                  <label>Fecha Final</label>
                                     <input
                                          type="date"
                                          name="fecha_final"
                                          class="form-control"
                                          value="{{ $fechaFinal }}">
                             </div>

                             <div >

                                 <button type="submit" class="btn btn-primary me-2">
                                     <i class="fas fa-search"></i> Buscar
                                 </button>
                             </div>

                             <div >
                                 <a href="{{ route('auditoria') }}"
                                     class="btn btn-secondary">
                                         Limpiar
                                 </a>

                             </div>



                     </form>

                  <!-- TABLA DE RESULTADOS -->




                                 <table class="table table-bordered table-hover align-middle" style="width: 100%">

                                     <thead class="table-light">
                                         <tr class="text-center text-muted">

                                             <th>No.</th>
                                             <th>Id Usuario</th>
                                             <th>Código Empleado</th>
                                             <th>Acción Realizada</th>
                                             <th>Detalle</th>
                                             <th>Módulo Afectado</th>
                                             <th>Fecha</th>

                                         </tr>
                                     </thead>

                                     <tbody>

                                         @forelse($registros as $registro)

                                             <tr>

                                                 <td>
                                                     {{ $loop->iteration }}
                                                 </td>

                                                 <td>
                                                     {{ $registro->id_usuario ?? '' }}
                                                 </td>

                                                 <td>
                                                     {{ $registro->cod_empleado ?? '' }}
                                                 </td>

                                                 <td>
                                                     {{ $registro->Accion_Realizada ?? '' }}
                                                 </td>

                                                 <td>
                                                     {{ $registro->Detalle ?? '' }}
                                                 </td>

                                                 <td>
                                                     {{ $registro->Modulo_Afectado ?? '' }}
                                                 </td>

                                                 <td>
                                                     {{ $registro->fecha_y_hora ?? '' }}
                                                 </td>

                                             </tr>

                                             @empty

                                             <tr>
                                                     <td colspan="5" class="text-center">
                                                         No hay registros disponibles
                                                     </td>
                                             </tr>

                                         @endforelse

                                     </tbody>

                                 </table>



                         {{-- PAGINACIÓN --}}
                         <div style="margin-top: 15px;">

                               {{ $registros->links('pagination::bootstrap-5') }}

                         </div>



         </div>

     </div>
 </div>


@endsection
