@extends('layouts.app')

@section('content')
@vite(['resources/css/bitacora.css', 'resources/js/bitacora.js'])

<div class="container py-4 security-page">
    <div class="row g-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

    <header class="topbar">
        <div class="brand">
            <img src="{{ asset('images/abejita.jpeg') }}" alt="Logo PumaGestión" class="brand-logo">


            <div class="brand-text">
                 <h1 class="brand-title">Bitácora del Sistema</h1>
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
    <form id="formBitacora" method="GET" action="{{ route('bitacora.index') }}" class="mb-3">

        <div>
            <label>Fecha de Inicio: </label>
            <input type="date" name="fecha_inicial" value="{{ request('fecha_inicial') }}">
        </div>

        <div>
            <label>Fecha final: </label>
            <input type="date" name="fecha_final" value="{{ request('fecha_final') }}">
        </div>

        <button type="submit" class="btn btn-primary me-2">Buscar</button>

        <div >
            <a href="{{ route('bitacora.index') }}"
               class="btn btn-secondary">
               Limpiar
            </a>

        </div>
    </form>

    <!-- TABLA DE RESULTADOS -->
    <table class="table table-bordered table-hover align-middle" style="width: 100%">
        <thead class="table-light">
            <tr class="text-center text-muted">
                <th>Responsable</th>
                <th>Operación Realizada</th>
                <th>Detalle</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bitacoras as $item)
                <tr>
                    <td>{{ $item->usuario_responsable ?? '' }}</td>
                    <td>{{ $item->operacion_realizada ?? '' }}</td>
                    <td>{{ $item->detalle ?? '' }}</td>
                    <td>{{ $item->fecha_y_hora ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No hay resultados</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 15px;">
        {{ $bitacoras->links('pagination::bootstrap-5') }}
    </div>


        </div>
    </div>
  </div>
</div>
@endsection
