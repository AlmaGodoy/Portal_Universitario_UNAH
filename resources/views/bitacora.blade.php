@extends('layouts.app')

@section('content')
@vite(['resources/css/bitacora.css', 'resources/js/bitacora.js'])

<div class="container">

    <h3>Bitácora del Sistema</h3>

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

        <button type="submit">Buscar</button>
    </form>

    <!-- TABLA DE RESULTADOS -->
    <table border="1" width="100%">
        <thead>
            <tr>
                <th>Usuario Responsable</th>
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
                    <td colspan="4">No hay resultados</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 15px;">
        {{ $bitacoras->links() }}
    </div>

</div>
@endsection
